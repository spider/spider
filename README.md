# Spider Graphs
[![Latest Version](https://img.shields.io/github/release/chrismichaels84/spider-graph.svg?style=flat-square)](https://github.com/chrismichaels84/spider-graph/releases)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Build Status](https://img.shields.io/travis/chrismichaels84/spider-graph/master.svg?style=flat-square)](https://travis-ci.org/chrismichaels84/spider-graph)
[![Coverage Status](https://coveralls.io/repos/chrismichaels84/spider-graph/badge.svg?branch=master)](https://coveralls.io/r/chrismichaels84/spider-graph?branch=master)
[![Total Downloads](https://img.shields.io/packagist/dt/michaels/spider-graph.svg?style=flat-square)](https://packagist.org/packages/michaels/spider-graph)

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
$connection = new Connection(new SomeDriver(), ['username' => 'me', 'etc' => 'etc']);
```

The connection is not activated (does not connect) at creation. So, you may alter it:
```php
$connection->setDriver(new OtherDriver());
$properties = $connection->getProperties();
$connection->setProperties(['new' => 'properties']);
$driverName = $connection->getDriverName(); // returns the classname of the current driver.
```

Connections also inherit from [michaels/data-manager](http://github.com/chrismichaels84/data-manager), so you have access to get(), set, has(), etc using dot notation for all properties.

### Managing Connections
For convenience, a connection manager is included. This allows you to store multiple connections and create connections from that list at will.

```php
$manager = new Michaels\Spider\Connections\Manager([
    'default' => 'default-connection',
    'connections' => [
        'default-connection' => [
            'driver'   => 'Full\Namespaced\Class\Name',
            'whatever' => 'options',
            'the' => 'driver',
            'needs' => 'to connect'
        ],
        'connection-two'     => [
            'driver'      => 'Some\Driver\Two',
            'credentials' => 'whatever',
            'is'       => 'needed'
        ]
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

## Inspired By
  * [Eloquent ORM](http://laravel.com/docs/5.0/eloquent)
  * [Hibernate OGM](http://hibernate.org/ogm/)
  * [Propel ORM](http://propelorm.org)
  * [Neo4j Client](https://github.com/neoxygen/neo4j-neoclient)
  * [Bulbflow](http://bulbflow.com/)
  
  
