<?php
require_once __DIR__ . '/../vendor/autoload.php';

if (file_exists(__DIR__ . '/../.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
    $dotenv->load();
}

try {
    $dsn = "pgsql:host=" . $_ENV['DB_HOST'] . 
           ";port=" . $_ENV['DB_PORT'] . 
           ";dbname=" . $_ENV['DB_NAME'];

    $conn = new PDO($dsn, $_ENV['DB_USER'], $_ENV['DB_PASS']);

    // Set error mode
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

$botToken = $_ENV['BOT_TOKEN'];
$groupID  = $_ENV['GROUP_ID'];
$topicID  = $_ENV['TOPIC_ID'];
?>
