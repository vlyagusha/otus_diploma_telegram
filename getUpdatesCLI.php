<?php
// todo: autoloader
// get updates realization 
use Longman\TelegramBot\Request;

require __DIR__ . '/vendor/autoload.php';
$COMMANDS_FOLDER = __DIR__.'/Commands/';
$config = yaml_parse_file('config.yaml');

try {
    // Create Telegram API object
    $telegram = new Longman\TelegramBot\Telegram($config['bot_api_key'], $config['bot_username']);

    $telegram->addCommandsPath($COMMANDS_FOLDER);

    // Enable MySQL
    $telegram->enableMySql($config['mysql_credentials']);
    
    // Handle telegram getUpdates request
    $updates = $telegram->handleGetUpdates();
    
//    $results = Request::sendToActiveChats(
//        'sendMessage', // Callback function to execute (see Request.php methods)
//        ['text' => 'Hey! Check out the new features!!'], // Param to evaluate the request
//        [
//            'groups'      => true,
//            'supergroups' => true,
//            'channels'    => false,
//            'users'       => true,
//        ]
//    );
    
//    foreach ($updates->getResult() as $user) {
//       $user = new User($user);
//       var_dump($user->text);
//       var_dump($user->chat->id);
////        if ($user->message['text'] === 'auff') {
////            $result = Request::sendMessage([
////                'chat_id' => $user->message['chat']['id'],
////                'text' => '',
////            ]);
////        }
//    }
    
} catch (Longman\TelegramBot\Exception\TelegramException $e) {
    
    // log telegram errors
   echo $e->getMessage();
}
