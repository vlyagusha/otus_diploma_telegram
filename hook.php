<?php

use GuzzleHttp\Client;
use Longman\TelegramBot\Request;
use Monolog\Logger;

require __DIR__ . '/vendor/autoload.php';
$COMMANDS_FOLDER = __DIR__.'/Commands/';
$config = yaml_parse_file('config.yaml');

try {
    // Create Telegram API object
    $telegram = new Longman\TelegramBot\Telegram($config['bot_api_key'], $config['bot_username']);

    $telegram->enableMySql($config['mysql_credentials']);

    $telegram->addCommandsPath($COMMANDS_FOLDER);

//    Longman\TelegramBot\TelegramLog::initialize(
//        new Monolog\Logger('telegram_bot', [
//            (new Monolog\Handler\StreamHandler(__DIR__ . "/my_bot_debug.log", Logger::DEBUG))->setFormatter(new Monolog\Formatter\LineFormatter(null, null, true)),
//            (new Monolog\Handler\StreamHandler(__DIR__ . "/my_bot_debug_error.log", Logger::ERROR))->setFormatter(new Monolog\Formatter\LineFormatter(null, null, true)),
//        ]),
//        new Monolog\Logger('telegram_bot_updates', [
//            (new Monolog\Handler\StreamHandler(__DIR__ . "/my_bot_debug_update.log", Logger::INFO))->setFormatter(new Monolog\Formatter\LineFormatter(
//                '[%datetime%] %channel%.%level_name%: %message% %context.user%' . PHP_EOL)),
//        ])
//    );

    $telegram->enableLimiter();
    
    // Handle telegram webhook request
    $telegram->handle();
} catch (Longman\TelegramBot\Exception\TelegramException $e) {
    Longman\TelegramBot\TelegramLog::error($e);
} catch (Longman\TelegramBot\Exception\TelegramLogException $e) {
    Longman\TelegramBot\TelegramLog::error($e);
}
