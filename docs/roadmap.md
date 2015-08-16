# Roadmap  
This is a *very* living document. Here, we collect our ideas for which direction to proceed in.
Join the conversation at http://github.com/spider/spider

## v0.3 Basic CRUD Query Builder
  * A DriverInterface Contract and abstract for creating drivers
  * A basic, fluent CommandBuilder that performs CRUD operations, but does not handle traversals or relationships (those come in 0.4)
  * Predictable and versatile response formats.
  * Fully documented, both at code-level and in guides at http://spider-ogm.readthedocs.org/
  * Transaction support
  
## v0.4 Traversal Query Builder
  * The Full Command Builder extends the Basic Command Builder by adding declarative traversals
  * Nested queries, trees, and paths
  * A solution for the n+1 problem (building a query that fetches a post with all its comments in one query).
  * A top-level `Spider` object for global configuration
  * Data-binding and security
  
## v0.4 GraphModels and OGM
  * Inspired by Eloquent and Propel
  * Allow for prepared queries that may be saved and reused as templates
  * Allow for:
    * explicit models `User::all()` `(new User([]))->save()`
    * or dynamic models `$spider->makeModel('label')`
    * or generic models `Spider\Models\Generic('label')`
  * Protects against Mass Assignment
  * Accessors and mutators
  * Smart hydration

## v0.5 Schema Builder
  * Database agnostic
  * Extensible for specific datastores

## v0.6 Profiling and Scaling
  * Multiple connections, load balancing, distributing
  * Query profiling

## Beyond and Misc
  * Allow for extensions to give the spider new functionality.
  * Be able to integrate third-party loggers, profilers, and caching
  * SSL support