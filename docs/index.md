# Spider OGM
A simple, flexible, and beautiful graph-data abstraction for php.

Why "Spider?" Because spiders crawl over webs, and webs look like graphs. Walk your data.

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