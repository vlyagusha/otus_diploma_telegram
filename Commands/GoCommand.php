<?php

namespace Longman\TelegramBot\Commands\UserCommands;

use Longman\TelegramBot\Commands\SystemCommands\CallbackqueryCommand;
use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Conversation;
use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Entities\Keyboard;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Request;

/**
 * User "/go" command
 *
 */
class GoCommand extends UserCommand
{

    const START_PHOTO = '/var/www/bot/start.jpg';
    const TRY_AGAIN_PHOTO = '/var/www/bot/try_again.jpg';
    const FILMS_RECOMMENDATIONS_COUNT = 6;


    /**
     * @var string
     */
    protected $name = 'go';

    /**
     * @var string
     */
    protected $description = 'С помощью этой функции вы можете получить рекомендации кинокартин, по вашим предпочтениям.';

    /**
     * @var string
     */
    protected $usage = '/go';

    /**
     * @var string
     */
    protected $version = '0.1.0';

    /**
     * @var bool
     */
    protected $need_mysql = true;

    /**
     * @var bool
     */
    protected $private_only = true;

    /**
     * Conversation Object
     *
     * @var \Longman\TelegramBot\Conversation
     */
    protected $conversation;


    /**
     * @param string $text
     * @param array $favouritesFilms
     * @param array $data
     * @return \Longman\TelegramBot\Entities\ServerResponse
     * @throws TelegramException
     */
    public function addFilmToFavourites($text, &$favouritesFilms, &$data, $movies)
    {
        $movie = array_search($text, $movies);

        if (in_array($text, array_column($favouritesFilms, 'title'))){
            $index = array_search($text, array_column($favouritesFilms, 'title'));
            unset($favouritesFilms[$index]);
            $favouritesFilms = array_values($favouritesFilms);
        } else {
            $favouritesFilms[] = [
                'id' => $movie, 
                'title' => $text
            ];
        }
        $this->conversation->update();
        $countdownFavouriteFilms = self::FILMS_RECOMMENDATIONS_COUNT - count($favouritesFilms);
        $renderTextForCountdown = $countdownFavouriteFilms > 0 ?
            'Для возможности получить рекомендации осталось выбрать '. $countdownFavouriteFilms. ' фильма(ов)' 
            : '';
        
        $choosedFilms = implode(', ', array_column($favouritesFilms, 'title'));
        $data['text'] = 'Выбранные фильмы: ' . $choosedFilms . PHP_EOL . PHP_EOL .
            'Для поиска нового фильма введите его название в строке.' . PHP_EOL .
            'Для удаления фильма введите его название повторно.' . PHP_EOL.
            $renderTextForCountdown;
        
        return Request::sendMessage($data);
    }

    /**
     * Command execute method
     *
     * @return \Longman\TelegramBot\Entities\ServerResponse
     * @throws \Longman\TelegramBot\Exception\TelegramException
     */
    public function execute()
    {
        $message = $this->getMessage();
        $chat    = $message->getChat();
        $user    = $message->getFrom();
        $text    = filter_var(trim($message->getText(true)), FILTER_SANITIZE_STRING);
        $chat_id = $chat->getId();
        $user_id = $user->getId();

        $data = [
            'chat_id' => $chat_id,
        ];

        if ($chat->isGroupChat() || $chat->isSuperGroup()) {
            //reply to message id is applied by default
            //Force reply is applied by default so it can work with privacy on
            $data['reply_markup'] = Keyboard::forceReply(['selective' => true]);
        }

        //Conversation start
        $this->conversation = new Conversation($user_id, $chat_id, $this->getName());

        $notes = &$this->conversation->notes;
        !is_array($notes) && $notes = [];

        $result = Request::emptyResponse();
        
        while (true) {
            
            if ($text === '') {
                $this->conversation->update();

                Request::sendPhoto([
                    'chat_id' => $chat_id,
                    'photo'   => Request::encodeFile(self::START_PHOTO),
                ]);

                $data['text'] = 'Приветствую Вас в системе по подбору рекомендаций кинокартин, на основе ваших предпочтений!'. PHP_EOL.
                    'Для подбора рекомендаций, выберите как минимум 6 фильмов.'. PHP_EOL.
                    'Введите название фильмов, которые понравились вам больше всего:'; 
                $data['reply_markup'] = Keyboard::remove(['selective' => true]);
                
                $result = Request::sendMessage($data);
                break;
            }
            
            // обновления массива фильмов для проверки ввода
            if (!in_array($text, array_values($notes['movies']))) {
                $movies = ApiMethods::getMoviesByNameFromApi($text);
                $notes['movies'] = array_column($movies, 'title', 'id');
                $notes['movie_title'] = $text;
                $this->conversation->update();      
            }
            
            if (!count($notes['movies'])) {
                FilmsCommand::moviesFromApiChecking($chat_id);
            }

            if (count($notes['favouriteFilms']) > 4) {

                $inline_keyboard = new InlineKeyboard(
                    [
                        ['text' => 'Получить рекомендации', 'callback_data' => CallbackqueryCommand::CMD_RECOMMENDATIONS],
                        ['text' => 'Очистить предпочтения', 'callback_data' => CallbackqueryCommand::CMD_CLEAR],
                    ]
                );
                $encodedKeyboard = json_encode($inline_keyboard);

                $data = [
                    'chat_id'      => $chat_id, 
                    'reply_markup' => $encodedKeyboard
                ];
            }
            
            if ($text === $notes['movie_title']) {

                if (!in_array($text, array_values($notes['movies']))) {
                    FilmsCommand::SendMoviesAsButtons($notes['movie_title'], $chat);
                } else {
                    $this->addFilmToFavourites($text, $notes['favouriteFilms'], $data, $notes['movies']);
                    break;
                }
            } else {
                $this->addFilmToFavourites($text, $notes['favouriteFilms'], $data, $notes['movies']);
                break;
            }
            
            break;
        }
        
        return $result;
    }
}
