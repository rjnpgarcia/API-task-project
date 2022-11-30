<?php
// Composer's Autoload (for classes)
require dirname(__DIR__) . "/vendor/autoload.php";

// Error/Exception Handler
set_error_handler("ErrorHandler::handleError");
set_exception_handler("ErrorHandler::handleException");

// Database Connection Details using .env
$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

// Header for json type
header("Content-type: application/json; charset=UTF-8");
