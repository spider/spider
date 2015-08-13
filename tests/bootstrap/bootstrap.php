<?php
require __DIR__ . '/../../vendor/autoload.php';

/* Create Databases Fixtures */
$drivers = [
    'orient' => null,
//    'neo' => null,
//    'gremlin' => null,
];

foreach ($drivers as $name => $driver) {
    $fixtureClass = "\\Spider\\Test\\Fixtures\\" . ucfirst($name);
    $drivers[$name] = new $fixtureClass();

    $drivers[$name]->setup();

    register_shutdown_function([&$drivers[$name], 'teardown']);
}
