<?php

namespace Longman\TelegramBot\Commands\UserCommands;
require 'ApiHandler.php';
require 'ApiMethods.php';

use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Entities\Keyboard;
use Longman\TelegramBot\Request;
use Longman\TelegramBot\Commands\UserCommands\ApiMethods;
use Longman\TelegramBot\Commands\UserCommands\ApiHandler;

class FilmsCommand extends UserCommand {
    
    /**
     * @var string
     */
    protected $name = 'films';

    /**
     * @var string
     */
    protected $description = '';

    /**
     * @var string
     */
    protected $version = '0.1.0';
    
    private function parseUserString() 
    {
        $message = $this->getMessage();
        $text    = filter_var(trim($message->getText(true)), FILTER_SANITIZE_STRING);
        
        return $text;
    }
    
    private static function renderTelegramKeyboardForFilmsChoosing($response) {
        
        $items = array_map(function ($film) { 
            return [
                'text'          => $film['title'],
                'id'            => $film['id'], 
            ];
        }, $response);

        $max_per_row  = 3;
        $per_row      = sqrt(count($items));
        $rows         = array_chunk($items, $per_row === floor($per_row) ? $per_row : $max_per_row);

        $keyboard = [
            'keyboard' => $rows
        ];

        $keyboard = json_encode($keyboard);
        
        return $keyboard;
    }

    public static function SendMoviesAsButtons($text, $chat)
    {
        
        $chat_id = $chat->getId();
        $movies = ApiMethods::getMoviesByNameFromApi($text);
        $keyboard = self::renderTelegramKeyboardForFilmsChoosing($movies);
        
        $data = [
            'chat_id' => $chat_id,
            'text'    => 'Выберите свои любимые фильмы:',
            'reply_markup' => $keyboard
        ];

        Request::sendMessage($data);
    }
    
    public static function renderMessageWithRecommendationsForSend($user_id, $favouriteFilmsIDs)
    {
        $text = '';

        $recommendations = ApiMethods::getRecommendationsFromApi($user_id, $favouriteFilmsIDs);
        foreach ($recommendations as $recommendation) {
            $text .= '<a href="'.$recommendation['trailerLink'].'"><b>'.$recommendation['title'].'</b></a>' . PHP_EOL;
        }
        
        return $text;
    }
    
    public static function moviesFromApiChecking($chat_id)
    {
        Request::sendPhoto([
            'chat_id' => $chat_id,
            'photo'   => Request::encodeFile(GoCommand::TRY_AGAIN_PHOTO),
        ]);
        
        $data['chat_id']      = $chat_id;
        $data['text']         = 'К сожалению, по вашему запросу ничего не найдено...'. PHP_EOL
            .'Повторите поиск:';
        $data['reply_markup'] = Keyboard::remove(['selective' => true]);

        Request::sendMessage($data);
    }
    
    public function execute() 
    {
        $chat_id = $this->getMessage()->getChat()->getId();
        $text = $this->parseUserString();
        $movies = ApiMethods::getMoviesByNameFromApi($text);
        $keyboard = self::renderTelegramKeyboardForFilmsChoosing($movies);
        
        $data = [
            'chat_id' => $chat_id,
            'reply_markup' => $keyboard
        ];

        return Request::sendMessage($data);
    }


}
