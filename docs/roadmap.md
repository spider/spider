# Roadmap  
This is a *very* living document. Here, we collect our ideas for which direction to proceed in.
Join the conversation at http://github.com/spider/spider

## v0.3 Basic CRUD Query Builder
  * A DriverInterface Contract and abstract for creating drivers
  * A basic, fluent CommandBuilder that performs CRUD operations, but does not handle traversals or relationships (those come in 0.4)
  * Predictable and versatile response formats.
  * Fully documented, both at code-level and in guides at http://spider-ogm.readthedocs.org/
  * Transaction support
  * Testing and Documentation foundations
  
## v0.4 Traversal Query Builder
  * A Command Builder that handles relationships, complex queries, and traversals.
  * Nested queries, trees, and paths
  * A solution for the n+1 problem (building a query that fetches a post with all its comments in one query).
  * A top-level `Spider` object for global configuration
  
## v0.5 Schema Builder
  * Database agnostic schema builder.
  * Data migrations and seeding, final fixtures.
  * Include sample graphs.
  * Extensible for specific datastores.
  * Rebuild documentation.
  * System wide ioc container for events, logging, caching, etc.
  
## v0.6 GraphModels and OGM
  * Inspired by Eloquent and Propel
  * Allow for saved queries that may be saved and reused as templates
  * Allow for:
    * explicit models `User::all()` `(new User([]))->save()`
    * or dynamic models `$spider->makeModel('label')`
    * or generic models `Spider\Models\Generic('label')`
  * Protects against Mass Assignment
  * Accessors and mutators
  * Smart hydration

## 0.7 Security and Production Features
  * Data-binding and injection.
  * Session support.
  * SSL support
  * Solid bug-scrub.
  * First production ready release

## v0.8 Profiling and Scaling
  * Multiple connections, load balancing, distributing.
  * Query profiling

## Beyond and Misc
  * Allow for extensions to give the spider new functionality.
  * Be able to integrate third-party loggers, profilers, and caching (foundation laid in 0.5)