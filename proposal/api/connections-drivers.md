# Api for Connections and Drivers
```php
$config = [
    'driver' => 'orientdb' // Default driver
    'drivers' => [
        'orientdb' => 'OrientDb\OrientDriver2'
        'rexster' => 'Spider\Drivers\Rexster\ResterProDriver'
    ],
    
    'connection' => 'my_connection',
    'connections' => [
        'my_connection' => [
            'driver' => 'orientdb',
            'url' => 'whatever.com',
            'other' => 'settings'
        ],
        'another_connection' => [
            'driver' => 'Vendor\Another\Driver',
            'other' => 'settings'
        ],
    ],
];

$manager = new Manager($config);
$manager->make(); // default connection
$manager->make('specific') // from config
$manager->make(new VendorDriver(), $credentials);

$connection->getProperties(); // returns what it was instantiated with

$connection = $manager->make();
$connection->open();
$connection->close();
$connection->driver->operations();

// Passed To Driver
$connection->addVertex('name', $properties);
$connection->addEdge('name', $beginningId, $endingId, $properties);
$connection->updateVertex();
$connection->updateEdge();
$connection->getVertex()->property('name');
$connection->getEdge()->property('name');
$connection->dropVertex();
$connection->dropEdge();
```