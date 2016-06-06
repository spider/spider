<?php
use Everyman\Neo4j\Client;
use PhpOrient\PhpOrient;

require __DIR__ . "/../vendor/autoload.php";

/* Set error display appropriately */
ini_set('error_reporting', E_ALL);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');

/* Load Environment Credentials */
if (getenv('TRAVIS')) {
    // We are in travis
    $dotenv = new Dotenv\Dotenv(__DIR__, '.env.travis');

}elseif (getenv('SPIDER_DOCKER')) {
    // We are in spider's special docker php container
    $dotenv = new Dotenv\Dotenv(__DIR__, '.env.docker');

} elseif (file_exists(__DIR__ . '/../.env')) {
    // We are not in our docker container, and the user has supplied credentials
    $dotenv = new Dotenv\Dotenv(__DIR__ . '/../');

} else {
    // We are not in our docker container and there are no credentials supplied
    // Use the from .env.travis (localhost)
    echo "WARNING: No Database Credentials Supplied. Please use the supplied docker container or add a .env file.\n";
    $dotenv = new Dotenv\Dotenv(__DIR__, '.env.travis');
}
$dotenv->load();

//echo getenv('GREMLIN_HOSTNAME');


/* Wait for DB services to be started */
// @todo: make this more robust. Allow for comma-separated values, etc
// @todo: extract to a separate class utility
if (getenv('WAIT_FOR')) {

    // Create the clients
    $neo_client = new Client(getenv('NEO4J_HOSTNAME'), getenv('NEO4J_PORT'));
    $neo_client->getTransport()
        ->setAuth(getenv('NEO4J_USERNAME'), getenv('NEO4J_PASSWORD'));

    $orient_client = new PhpOrient();
    $orient_client->configure([
        'hostname' => getenv('ORIENTDB_HOSTNAME'),
        'port' => getenv('ORIENTDB_PORT'),
        'username' => getenv('ORIENTDB_USERNAME'),
        'password' => getenv('ORIENTDB_PASSWORD'),
    ]);

    // Wait for good responses
    $attempts = 0;
    while ($attempts < 60) { // try for two minutes-ish
        try {
            $neo_client->getServerInfo();
            $orient_client->connect();
            break;
        } catch (Exception $e) {

            if ($attempts === 59) {
                echo "WARNING: Database services did not startup after 2 minutes. Aborting tests.\n";
                exit(2);
            } else {
                echo "Waiting for database services to startup...\n";
                $attempts++;
                sleep(2);
            }
        }
    }

    $time_to_startup = $attempts * 2;
    echo "All database services started after {$time_to_startup} seconds! Moving on.\n";
}