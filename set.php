<?php

require __DIR__ . '/vendor/autoload.php';
$COMMANDS_FOLDER = __DIR__.'/Commands/';
$config = yaml_parse_file('config.yaml');

try {
    // Create Telegram API object
    $telegram = new Longman\TelegramBot\Telegram($config['bot_api_key'], $config['bot_username']);

    // Set webhook
    $result = $telegram->setWebhook($config['webhook_url'], ['certificate' => $config['certificate_path']]);

    if ($result->isOk()) {
        echo $result->getDescription();
    }
} catch (Longman\TelegramBot\Exception\TelegramException $e) {
    // log telegram errors
    echo $e->getMessage();
}


