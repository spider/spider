# Getting Started

## Install
Via Composer
``` bash
$ composer require spider/spider
```

## Getting Started
The `master` branch contains stable code, though not necessarily ready for production. 
It follows the milestones outlined in the proposal. 
The `develop` branch is a step ahead and may me unstable right now. As we near v0.5 and the first production ready (though feature incomplete) release, the develop branch will stabilize.

### Connections
A connection holds the driver to a datastore and whatever settings that driver needs to connect (username, host, port, etc). 

All connections implement `Spider\Connections\ConnectionInterface` and must be passed Driver Object and array of credentials at creation. They may also be passed optional configuration.

```php
$credentials = [
    'host' => 'localhost',
    'port' => 1234,
    // etc
];

$connection = new Connection(new SomeDriver(), $credentials);
```

Connections also inherit from [michaels/data-manager](http://github.com/chrismichaels84/data-manager), so you have access to get(), set, has(), etc using dot notation for all properties.

### Managing Connections
For convenience, a connection manager is included. This allows you to store multiple connections and create connections from that list at will.

When using the connection manager, you must give it your database credentials. You can store multiple sets of credentials and create connections for each one.

The credentials include *at least* a `default` driver name, and the credentials for that driver.

```php
$manager = new Spider\Connections\Manager([
    'default' => 'default-connection',
    'default-connection' => [
        'driver'   => 'Drivers\Full\Namespace\Class',
        'whatever' => 'options',
        'the' => 'driver',
        'needs' => 'to connect'
    ],
    'connection-two' => [
        'driver'      => 'Some\Other\Full\Namespace\Class',
        'credentials' => 'whatever',
        'is'       => 'needed'
    ]
]);

$defaultConnection = $manager->make();
$connectionTwo = $manager->make('connection-two');
```

The connection manager also inherits from [michaels/data-manager](http://github.com/chrismichaels84/data-manager), 
so you have access to get(), set, has(), etc using dot notation for all connections.

```php
$manager->get('connections.default-connection');
$manager->add('connection.new-connection', ['my' => 'connection']);
//etc.
```

### Fetching and Caching Connections
Anytime you `make()` a connection it will be cached so you can draw the same connection again (say later in your application).

You can get that cached connection via `fetch()` which will also create a new connection if it has not already been `make()`d

```php
$manager->fetch(); // Will return cached default connection, or create then cache it before returning
$manager->fetch('connection-name'); // same
```

### Using the Driver
For now, the only supported driver is an `OrientDriver` for OrientDB. More are on the way.

You use the driver through the connection. Once you have a connection setup, you can `open()` it.

When sending queries or commands, be sure to use an instance of the `QueryInterface` to pass to the connection.
The following methods work with the datastore:
```php
$sendCommand = new Spider\Queries\Query("WHATEVER THE SCRIPT IS");
//$sendCommand = new Spider\Queries\Query("SELECT FROM Cats WHERE name = 'Oreo'");

$connection->open(); // uses the credentials given to the `Connection` when created
$response = $connection->executeReadCommand(QueryInterface $sendCommand); // for read-only commands like SELECT
$response = $connection->executeWriteCommand(QueryInterface $sendCommand); // for write commands (INSERT, UPDATE, DELETE)

// or you can run a command without waiting for a response
$connection->runWriteCommand(QueryInterface $sendCommand);

// Close the connection when you are done
$connection->close();
```