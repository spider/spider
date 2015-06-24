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
A connection holds the driver to a datastore and whatever settings that driver needs to connect (username, host, port, etc). All connections implement `Michaels\Spider\Connections\ConnectionInterface` and must be passed Driver Object and array of paremeters at creation.

```php
$connection = new Connection(new SomeDriver(), ['username' => 'me', 'etc' => 'etc'], $optionalConfigs);
```

The connection is not activated (does not connect) at creation. So, you may alter it:
```php
$connection->setDriver(new OtherDriver());
$properties = $connection->getProperties();
$connection->setProperties(['new' => 'properties']);
$driverName = $connection->getDriverName(); // returns the classname of the current driver.
$connection->set('config.whatever', 'value');
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
    ],
    'config' => [
        'these' => 'are',
        'optional' => true,
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

### Configuration
You store configuration inside of the Connection Manager and it will propagate through the system. Otherwise, you must pass a config array into the connection.

#### Return Objects
By default, any `connection` will convert the driver's native response into an instance of `Michaels\Graphs\Graph`.

You may specify a **different** return object:
```php
$manager = new Michaels\Spider\Connections\Manager([
    'default' => 'default-connection',
    'default-connection' => [...]
    'config' => [
        'return-object' => 'Full\Class\Name\Here',
    ]
]);
```
which will pass the traversable response into the constructor.

If your return object needs to use a **specific method** to load/hydrate/fill
```php
    'config' => [
        'return-object' => [Full\Class\Name\Here',
        'map-method' => 'methodName'
    ]
```
which will pass the traversable response into that method after creating a new response object.

Lastly, you may also choose to get back the **native** response
```php
    'config' => [
        'return-object' => 'native'
    ]
```

### Exceptions
If you try to `make()` a connection that doesn't exist, a `ConnectionNotFoundException` will be thrown.

## Inspired By
  * [Eloquent ORM](http://laravel.com/docs/5.0/eloquent)
  * [Hibernate OGM](http://hibernate.org/ogm/)
  * [Propel ORM](http://propelorm.org)
  * [Neo4j Client](https://github.com/neoxygen/neo4j-neoclient)
  * [Bulbflow](http://bulbflow.com/)
  
  
