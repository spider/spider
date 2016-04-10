# Spider Graphs

[![Managed with ZenHub!](https://raw.githubusercontent.com/ZenHubIO/support/master/zenhub-badge.png)](https://zenhub.io)
[![Join the chat at https://gitter.im/spider/spider](https://badges.gitter.im/spider/spider.svg)](https://gitter.im/spider/spider?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge&utm_content=badge)
[![Latest Version](https://img.shields.io/github/release/spider/spider.svg?style=flat-square)](https://github.com/spider/spider/releases)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Build Status](https://travis-ci.org/spider/spider.svg?branch=master)](https://travis-ci.org/spider/spider)
[![Coverage Status](https://coveralls.io/repos/spider/spider/badge.svg?branch=master&service=github)](https://coveralls.io/github/spider/spider?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/spider/spider/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/spider/spider/?branch=master)
[![Total Downloads](https://img.shields.io/packagist/dt/spider/spider.svg?style=flat-square)](https://packagist.org/packages/spider/spider)

[![SensioLabsInsight](https://insight.sensiolabs.com/projects/dc73805e-2a58-4007-a49e-506281e309ce/big.png)](https://insight.sensiolabs.com/projects/dc73805e-2a58-4007-a49e-506281e309ce)

A simple, flexible, and beautiful graph-data abstraction for php.

Why "Spider?" Because spiders crawl over webs, and webs look like graphs. Walk your data.

This readme is very limited.
See **[the full documentation](http://spider-ogm.readthedocs.org/en/latest/)** for a guidebook.

## Current Version
Spider is still under active development. All releases are well-tested and stable, though maybe not ready for production yet.
The current version is v0.3.0 - which includes a basic command builder, connections, and drivers.
For a roadmap see [the full documentation](http://spider-ogm.readthedocs.org/en/latest/).
Please use spider anywhere you can and open issues around bugs or edge cases.

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
Check out **[the full documentation](http://spider-ogm.readthedocs.org/)**.

## Inspired By
  * [Eloquent ORM](http://laravel.com/docs/5.0/eloquent)
  * [Hibernate OGM](http://hibernate.org/ogm/)
  * [Propel ORM](http://propelorm.org)
  * [Neo4j Client](https://github.com/neoxygen/neo4j-neoclient)
  * [Bulbflow](http://bulbflow.com/)
  
## Contributing
Contributions are welcome and will be fully credited. Please see [CONTRIBUTING](contributing.md) for details.

### Security
If you discover any security related issues, please email spiderogm@gmail.com instead of using the issue tracker.

### Credits
- [Michael Wilson](https://github.com/chrismichaels84)
- [PommeVerte](https://github.com/PommeVerte)
- Open an issue to join in!

### License
The MIT License (MIT). Please see [License File](license.md) for more information.
  
