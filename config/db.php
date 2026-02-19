<?php
require_once __DIR__ . '/../vendor/autoload.php';

if (file_exists(__DIR__ . '/../.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
    $dotenv->load();
}

$conn = new mysqli($_ENV['DB_HOST'], $_ENV['DB_USER'], $_ENV['DB_PASS'], $_ENV['DB_NAME']);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$botToken = $_ENV['BOT_TOKEN'];
$groupID  = $_ENV['GROUP_ID'];
$topicID  = $_ENV['TOPIC_ID'];
?>
