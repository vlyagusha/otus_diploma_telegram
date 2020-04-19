<?php
// Load composer

use GuzzleHttp\Client;
use Longman\TelegramBot\Request;

require __DIR__ . '/vendor/autoload.php';
$COMMANDS_FOLDER = __DIR__.'/Commands/';
$config = yaml_parse_file('config.yaml');

try {
    // Create Telegram API object
    $telegram = new Longman\TelegramBot\Telegram($config['bot_api_key'], $config['bot_username']);

    // $telegram->enableMySql($config['mysql_credentials']); настроить mysql на машинке

    $telegram->addCommandsPath($COMMANDS_FOLDER);

//    Request::setClient(new Client([
//        'base_uri' => 'https://api.telegram.org',
//        'proxy'    => 'socks5://213.136.89.190:28573',
//    ]));
//    РОСКОМНАДЗОР
    
    // Handle telegram webhook request
    $telegram->handle();
} catch (Longman\TelegramBot\Exception\TelegramException $e) {
    // Silence is golden!
    // log telegram errors
    echo $e;
} catch (Longman\TelegramBot\Exception\TelegramLogException $e) {
    // Silence is golden!
    // Uncomment this to catch log initialisation errors
    echo $e;
}
