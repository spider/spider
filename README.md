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
  * A filter/query builder that doesn't make your mind go nuts.
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

All connections implement `Michaels\Spider\Connections\ConnectionInterface` and must be passed Driver Object and array of credentials at creation. They may also be passed optional configuration.

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

```php
$manager = new Michaels\Spider\Connections\Manager([
    'default' => 'default-connection',
    'default-connection' => [
        'driver'   => 'Full\Namespaced\Class\Name',
        'whatever' => 'options',
        'the' => 'driver',
        'needs' => 'to connect'
    ],
    'connection-two' => [
        'driver'      => 'Some\Driver\Two',
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
$query = new Michaels\Spider\Queries\Query("WHATEVER THE SCRIPT IS");
//$query = new Michaels\Spider\Queries\Query("SELECT FROM Cats WHERE @rid = #13:1");

$connection->open(); // uses the credentials given to the `Connection` when created
$response = $connection->executeReadCommand(QueryInterface $query); // for read-only queryies like SELECT
$response = $connection->executeWriteCommand(QueryInterface $query); // for write commands (INSERT, UPDATE, DELETE)

// or you can run a command without waiting for a response
$connection->runWriteCommand(QueryInterface $query);

// Close the connection when you are done
$connection->close();
```

### Exceptions
If you try to `make()` a connection that doesn't exist, a `ConnectionNotFoundException` will be thrown.

## Inspired By
  * [Eloquent ORM](http://laravel.com/docs/5.0/eloquent)
  * [Hibernate OGM](http://hibernate.org/ogm/)
  * [Propel ORM](http://propelorm.org)
  * [Neo4j Client](https://github.com/neoxygen/neo4j-neoclient)
  * [Bulbflow](http://bulbflow.com/)
  
  
