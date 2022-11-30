<?php

declare(strict_types=1);

// ini_set("display_errors", "On");

require __DIR__ . "/bootstrap.php";

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

$parts = explode("/", $path);
$resource = $parts[3];

$id = $parts[4] ?? null;

if ($resource != "tasks") {
    //header("{$_SERVER['SERVER_PROTOCOL']} 404 Not Found");
    http_response_code(404);
    exit;
}

// Database Connection
$database = new Database($_ENV['DB_HOST'], $_ENV['DB_NAME'], $_ENV['DB_USER'], $_ENV['DB_PASS']);

// API Authentication
$user_gateway = new UserGateway($database);
$codec = new JWTCodec($_ENV['SECRET_KEY']);

$auth = new Auth($user_gateway, $codec);
if (!$auth->authenticateAccessToken()) {
    exit;
}

$user_id = $auth->getUserId();


$task_gateway = new TaskGateway($database);
// Task Controller
$controller = new TaskController($task_gateway, $user_id);
$controller->processRequest($_SERVER['REQUEST_METHOD'], $id);
