# Spider Graphs
[![Latest Version](https://img.shields.io/github/release/chrismichaels84/spider-graph.svg?style=flat-square)](https://github.com/chrismichaels84/spider-graph/releases)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Build Status](https://travis-ci.org/chrismichaels84/Spider-Graph.svg?branch=master)](https://travis-ci.org/chrismichaels84/Spider-Graph)
[![Coverage Status](https://coveralls.io/repos/chrismichaels84/Spider-Graph/badge.svg?branch=master)](https://coveralls.io/r/chrismichaels84/Spider-Graph?branch=master)
[![Total Downloads](https://img.shields.io/packagist/dt/michaels/spider.svg?style=flat-square)](https://packagist.org/packages/michaels/spider)

[![SensioLabsInsight](https://insight.sensiolabs.com/projects/374720ec-b7db-47fc-b958-cc240cf06fbb/big.png)](https://insight.sensiolabs.com/projects/374720ec-b7db-47fc-b958-cc240cf06fbb)

A simple, flexible, and generic graph-data abstraction for php.

Why "Spider?" Because spiders crawl over webs, and webs look like graphs. Walk your data.

**This is in [proposal](proposal/overview.md) stage right now**. The api is being worked out.
Pull Requests against the proposal documents are welcome from all, not just graph junkies.

Please browse through the [proposal](proposal/overview.md) to see the work in progress. Begin with proposal/overview.md

## Goals
  * Framework agnostic, generic package using composer.
  * Community-driven, best practices code (DRY, SOLID, PHP The Right Way, PSRs, Tinkerpop, Testing, etc.)
  * An easy transition from SQL or Mongo
  * Simple, fluent, and consistent API
  * An Object-Graph-Mapper and Models inspired by Eloquent, Propel, and Monga.
  * A filter/sendCommand builder that doesn't make your mind go nuts.
  * Simple drivers to connect to specific graph databases (orient, neo4j, titat, etc) or other datastores
  * Handle multiple connections
  * Validation, filtering, security, and performance.
  * Extensible and configurable (e.g. for caching).

## Install
Via Composer
``` bash
$ composer require michaels/spider
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

Drivers follow a convention. Each driver has its own namespace and directly under that namespace is a class called `Driver` plus whatever other classes the driver needs. You don't have to worry about all this. All you have to include is the driver's namespace.

```php
$manager = new Spider\Connections\Manager([
    'default' => 'default-connection',
    'default-connection' => [
        'driver'   => 'Drivers\Full\Namespace',
        'whatever' => 'options',
        'the' => 'driver',
        'needs' => 'to connect'
    ],
    'connection-two' => [
        'driver'      => 'Some\Other\Full\Namespace',
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

#### Making a Driver
Making a driver is simple. Create a package with whatever namespace you want, e.g. `My\Awesome\Driver\For\Titan` and create a `Driver` class that implements the `DriverInterface`. You've only got a few methods to fill out. Take a look at OrientDriver for an example.

And submit a PR to let us know!

### Exceptions
If you try to `make()` a connection that doesn't exist, a `ConnectionNotFoundException` will be thrown.

### Tests
```bash
phpunit
```
Note that the OrientDriver tests are disabled for the auto-build Travis CI process. They require a working database. Until a better way of mocking or seeding a test database is found, just allow these tests to be skipped. I am keeping up with them on my development machine. Any suggestions are welcome!

## Inspired By
  * [Eloquent ORM](http://laravel.com/docs/5.0/eloquent)
  * [Hibernate OGM](http://hibernate.org/ogm/)
  * [Propel ORM](http://propelorm.org)
  * [Neo4j Client](https://github.com/neoxygen/neo4j-neoclient)
  * [Bulbflow](http://bulbflow.com/)
  
## Contributing
Contributions are welcome and will be fully credited. Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security
If you discover any security related issues, please email phoenixlabsdev@gmail.com instead of using the issue tracker.

## Credits
- [Michael Wilson](https://github.com/chrismichaels84)
- [PommeVerte](https://github.com/PommeVerte)
- Open an issue to join in!

## License
The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

  
  
