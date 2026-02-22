<?php
session_start();

function loadEnv($path) {
    if (!file_exists($path)) {
        return;
    }
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);
        if (!array_key_exists($name, $_SERVER) && !array_key_exists($name, $_ENV)) {
            putenv(sprintf('%s=%s', $name, $value));
            $_ENV[$name] = $value;
            $_SERVER[$name] = $value;
        }
    }
}

// Load .env
loadEnv(__DIR__ . '/../.env');

// Set PHP Memory Limit
$memoryLimit = getenv('PHP_MEMORY_LIMIT') ?: '512M';
ini_set('memory_limit', $memoryLimit);

$host = getenv('DB_HOST');
$dbname = getenv('DB_NAME');
$username = getenv('DB_USER');
$password = getenv('DB_PASS');

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

require_once 'functions.php';

// Load Site Settings
$settings = get_site_settings($pdo);
define('SITE_TITLE', $settings['site_title'] ?? 'NABARD');
define('SITE_SUP', $settings['site_sup'] ?? '(Nilambur)');
define('SITE_LOGO', $settings['logo_path'] ?? 'logo.png');
date_default_timezone_set($settings['timezone'] ?? 'Asia/Kolkata');
?>
