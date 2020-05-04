<?php
// Load composer
require_once __DIR__ . '/vendor/autoload.php';
$config = yaml_parse_file('config.yaml');

try {
    // Create Telegram API object
    $telegram = new Longman\TelegramBot\Telegram($config['bot_api_key'], $config['bot_username']);

    // Delete webhook
    $result = $telegram->deleteWebhook();

    if ($result->isOk()) {
        echo $result->getDescription();
    }
} catch (Longman\TelegramBot\Exception\TelegramException $e) {
    echo $e->getMessage();
}
