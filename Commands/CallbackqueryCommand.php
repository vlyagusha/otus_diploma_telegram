<?php
namespace Longman\TelegramBot\Commands\SystemCommands;

use Longman\TelegramBot\Commands\UserCommands\ApiMethods;
use Longman\TelegramBot\Commands\SystemCommand;
use Longman\TelegramBot\Commands\UserCommands\FilmsCommand;
use Longman\TelegramBot\Conversation;
use Longman\TelegramBot\Entities\Keyboard;
use Longman\TelegramBot\Request;

/**
 * Callback query command
 *
 * This command handles all callback queries sent via inline keyboard buttons.
 *
 * @see InlinekeyboardCommand.php
 */
class CallbackqueryCommand extends SystemCommand
{
    
    const CMD_RECOMMENDATIONS = 'recommendations';
    const CMD_CLEAR = 'clear';
    const CMD_GO = 'go';
    
    /**
     * @var string
     */
    protected $name = 'callbackquery';

    /**
     * @var string
     */
    protected $description = 'Reply to callback query';

    /**
     * @var string
     */
    protected $version = '1.1.1';

    /**
     * Conversation Object
     *
     * @var \Longman\TelegramBot\Conversation
     */
    protected $conversation;

    /**
     * Command execute method
     *
     * @param Conversation $conversation
     * @throws \Longman\TelegramBot\Exception\TelegramException
     */
    
    public function execute() 
    {
        $callback_query    = $this->getCallbackQuery();
        $callback_query_id = $callback_query->getId();
        $callback_data     = filter_var($callback_query->getData(), FILTER_SANITIZE_STRING);
        $chat              = $callback_query->getMessage()->getChat();
        $chat_id           = $chat->getId();
        $user              = $callback_query->getFrom();
        $user_id           = $user->getId();
        
        $data = [
            'chat_id' => $chat_id,
        ];

        $this->conversation = new Conversation($user_id, $chat_id, self::CMD_GO);
        $notes = &$this->conversation->notes;
        !is_array($notes) && $notes = [];

        $result = Request::emptyResponse();
        
        while (true) {
            
            if ($callback_data === self::CMD_RECOMMENDATIONS) {

                $data = [
                    'callback_query_id' => $callback_query_id,
                    'text'              => 'Получение ваших рекомендаций...',
                    'show_alert'        => $callback_data === 'thumb up',
                    'cache_time'        => 5,
                ];

                Request::answerCallbackQuery($data);

                $favouriteFilmsIDs = array_column($notes['favouriteFilms'], 'id');
                $recommendationsText = FilmsCommand::renderMessageWithRecommendationsForSend($user_id, $favouriteFilmsIDs);
                unset($data);
                
                $data = [
                    'chat_id' => $chat_id,
                    'text'    => $recommendationsText,
                    'parse_mode' => 'HTML'
                ];

                Request::sendMessage($data);
                return false;
            }
            
            if ($callback_data === self::CMD_CLEAR) {
                
                $notes['favouriteFilms'] = [];
                $this->conversation->update();
                $data['reply_markup'] = Keyboard::remove(['selective' => true]);
                ApiMethods::deletePreferences($user_id);

                $data = [
                    'callback_query_id' => $callback_query_id,
                    'text'              => 'Ваши предпочтения очищены.',
                    'show_alert'        => $callback_data === 'thumb up',
                    'cache_time'        => 5,
                ];

                Request::answerCallbackQuery($data);

                return false;
            }
            
            return false;
        }
        
        return $result;
    }
}
