# Spider Graphs
[![Latest Version](https://img.shields.io/github/release/chrismichaels84/spider-graph.svg?style=flat-square)](https://github.com/chrismichaels84/spider-graph/releases)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Build Status](https://travis-ci.org/chrismichaels84/Spider-Graph.svg?branch=master)](https://travis-ci.org/chrismichaels84/Spider-Graph)
[![Coverage Status](https://coveralls.io/repos/chrismichaels84/Spider-Graph/badge.svg?branch=master)](https://coveralls.io/r/chrismichaels84/Spider-Graph?branch=master)
[![Total Downloads](https://img.shields.io/packagist/dt/michaels/spider.svg?style=flat-square)](https://packagist.org/packages/michaels/spider)

[![SensioLabsInsight](https://insight.sensiolabs.com/projects/374720ec-b7db-47fc-b958-cc240cf06fbb/big.png)](https://insight.sensiolabs.com/projects/374720ec-b7db-47fc-b958-cc240cf06fbb)

A simple, flexible, and generic graph-data abstraction for php.

Why "Spider?" Because spiders crawl over webs, and webs look like graphs. Walk your data.

This readme is very limited.
See **[the full documentation](http://http://spider-ogm.readthedocs.org/)** for full a full guidebook.

## Goals
  * Framework agnostic, community-driven with best practices.
  * Made *specifically* for highly-relational data.
  * An easy transition from SQL or Mongo.
  * Simple, fluent, and consistent API.
  * Simple drivers to connect to specific graph databases or other datastores.
  * Extensible and configurable.

## Current Features
  * Drivers for Gremlin Server, Neo4j, and OrientDB.
  * Easily [create your own one-class drivers](create-driver.md).
  * Fluent [Query Builder](command-builder.md) and Basic [Command Builder](command-builder.md).
  * Handle, cache, and manage multiple [connections](getting-started.md).
  * Consistent responses with various [formats](responses.md).
  
## Upcoming Features
  * Fluent traversals through the query and command builder.
  * Database agnostic Schema Builder.
  * Simple yet powerful Models and Object Graph Mappers
  * Graph data utilities (algorithms, etc.).
  * SSL support, data-binding, filters, validation, caching, logging, and more.
  * Extensible so you can use your own loggers, cachers, etc.
  
## Setup and Basic Usage
Install via Composer
``` bash
$ composer require spider/spider
```

The `master` branch contains stable code, though not necessarily ready for production.
The `develop` branch is a step ahead and may me unstable right now.

The simplest thing to do from there is use the query builder
```php
$connection = new Connection([
    'driver' => 'neo4j'
    'hostname' => 'localhost'
    'port' => 7474,
    'username' => 'root',
    'password' => 'root'
]);

$query = new Spider\Commands\Query($connection);

$characters = $query
    ->select('name, position, catch_phrase')
    ->from('characters')
    ->where('show', 'Firefly')
    ->orderBy('name')
    ->all();
    
foreach ($characters as $character) {
    $character->name; // Wash
    $character->position; // Pilot
    $character->catch_phrase; // "I am a leaf on the wind."
}
```

Of course, there's a lot more you can do.
Check out **[the full documentation](http://http://spider-ogm.readthedocs.org/)**.

## Inspired By
  * [Eloquent ORM](http://laravel.com/docs/5.0/eloquent)
  * [Hibernate OGM](http://hibernate.org/ogm/)
  * [Propel ORM](http://propelorm.org)
  * [Neo4j Client](https://github.com/neoxygen/neo4j-neoclient)
  * [Bulbflow](http://bulbflow.com/)
  
## Contributing
Contributions are welcome and will be fully credited. Please see [CONTRIBUTING](contributing.md) for details.

### Security
If you discover any security related issues, please email phoenixlabsdev@gmail.com instead of using the issue tracker.

### Credits
- [Michael Wilson](https://github.com/chrismichaels84)
- [PommeVerte](https://github.com/PommeVerte)
- Open an issue to join in!

### License
The MIT License (MIT). Please see [License File](license.md) for more information.
  
