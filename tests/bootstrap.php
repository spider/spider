<?php
use PhpOrient\PhpOrient;

require __DIR__ . "/../vendor/autoload.php";

/* Set error display appropriately */
ini_set('error_reporting', E_ALL);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');

/* Load Environment Credentials */
if (getenv('SPIDER_DOCKER')) {
    // We are in spider's special docker php container
    $dotenv = new Dotenv\Dotenv(__DIR__, '.env.docker');

} elseif (getenv('TRAVIS')) {
    // We are in travis
    $dotenv = new Dotenv\Dotenv(__DIR__, '.env.travis');

} elseif (file_exists(__DIR__ . '/../.env')) {
    // We are not in our docker container, and the user has supplied credentials
    $dotenv = new Dotenv\Dotenv(__DIR__ . '/../');

} else {
    // We are not in our docker container and there are no credentials supplied
    throw new \Exception("No Database Credentials Supplied. Please use the supplied docker container or add a .env file. Tests cancelled.");
}
$dotenv->load();

//e.g.: getenv('ORIENTDB_HOSTNAME');
