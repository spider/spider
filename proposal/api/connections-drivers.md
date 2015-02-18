# Api for Connections and Drivers

```php
$manager = new Manager($config);
$manager->make(); // default connection
$manager->make('specific') // from config
$manager->make(new VendorDriver(), $credentials);

$connection = $manager->make();
$connection->open();
$connection->close();
$connection->driver->operations();

// Passed To Driver
$connection->addVertex('name', $properties);
$connection->addEdge('name', $beginingId, $endingId, $properties);
$connection->updateVertex();
$connection->updateEdge();
$connection->getVertex()->property('name');
$connection->getEdge()->property('name');
$connection->dropVertex();
$connection->dropEdge();
```