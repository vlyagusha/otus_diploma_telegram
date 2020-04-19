<?php

namespace Longman\TelegramBot\Commands\UserCommands;
require __DIR__ . '/../ApiHandler.php';

use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Request;

class FilmsCommand extends UserCommand {
    
    /**
     * @var string
     */
    protected $name = 'films';

    /**
     * @var string
     */
    protected $description = 'This function search your favourite movies by title';

    /**
     * @var string
     */
    protected $usage = '/films <text>';

    /**
     * @var string
     */
    protected $version = '0.1.0';
    
    private function parseUserString() 
    {
        $message = $this->getMessage();
        $text    = trim($message->getText(true));
        
        return $text;
    }

    private function getMovieByNameFromApi($data)
    {
        $get_data = \ApiHandler::callAPI('GET', 'https://api.themoviedb.org/3/search/movie?api_key=5aabd3672ccd40399e41426a5f7b297f&language=en-US&page=1&include_adult=false&query='.$data, false);
        $response = json_decode($get_data, true); 

        $items = array_map(function ($film) {
            return [
                'text'          => $film['title'],
                'callback_data' => $film['title'],
            ];
        }, $response['results']);

        $items = array_slice($items, 0, 6);

        $max_per_row  = 3;
        $per_row      = sqrt(count($items));
        $rows         = array_chunk($items, $per_row === floor($per_row) ? $per_row : $max_per_row);
        
        return $rows;
    }
    
    public function execute() 
    {
        $chat_id = $this->getMessage()->getChat()->getId();
        $text = $this->parseUserString();
        
        if ($text === '') {
            $text = 'Command usage: ' . $this->getUsage();
        }

        $rows = $this->getMovieByNameFromApi($text);

        $keyboard = [
            'inline_keyboard' => $rows // todo: формирование кнопок -> отдельная функция
        ];
        
        $encodedKeyboard = json_encode($keyboard);
        
        $data = [
            'chat_id' => $chat_id,
            'text'    => 'Choose your favourite films',
            'reply_markup' => $encodedKeyboard
        ];

        return Request::sendMessage($data);
    }


}
