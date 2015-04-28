# Features and Milestones
This document breaks down the goals and workflow into feature sets with linear milestones, though no dates are given. Only semantic versions.

## v0.1 Manager and Connections
The connection manager creates and returns connections with drivers from a config array. 
  * `Connection\Manager` receives config array
    * `make()`s the default connection
    * `make()`s non-default connections
    * setters and getters
  * `Connection` holds credential information
    * sens most through to the driver
    * setters and getters
  
## v0.2 DriverInterface and Neo4j Driver (First Release)
The Driver itself makes all the CRUD transactions. The driver is accessed through the connections.
  * Simple one Class drivers
  * Contract Interface

## v0.3 Graphs and GraphCollections
  * Populate Graphs from array data, not only data sources
  * Extend `Illuminate\Support\Collection`
  * ->toGraphson()
  * Filter and validation
  * Driver results filtered into a Graph
  * A sane and simple way to explore the graph as a php object.
    * e.g. `$graph->michael['last_name']->married_to['years']->wife['previous_names']['maiden_name'];`
  * Built in ways to iterate through the stored graph or results (using closures or loops)
  * Accessors and Mutators
  * Graph Collections can be extended `Graph::extend('reverse', $closure)`
  
## v0.4 Queries
  * Return Graph with connection to datastore
  * Use Query independent of Models (Query::findOne())
  * Inspired by Eloquent and Propel
  * Allow for user to getRawResult() from queries
  * Allow for multiple (batch) queries as sent through an array
  * Allow for PreparedQueries() to be saved and reused as templates
  * Perhaps use Data Marshal [aura](https://github.com/auraphp/Aura.Marshal/tree/master)
  * ? Querybuilder to Gremlin Script to connection ?

## v0.4 GraphModels and OGM
Model driven OGM inspiered by popular ORMs.
  * Allow for explicit models or generic (included) model ($manager->buildGraph()) or Spider\Models\Generic('type')
  * Use the [DataMapper Patter](http://martinfowler.com/eaaCatalog/dataMapper.html)
  * GraphModels protects against Mass Assignment

## v0.5 Spider and Walker (First Beta Release)
Top level Api wrapper meant to encapsulate for easy use.
  * walk data currently held in a Graph()
  * walk data as a method of querying against a connection stored in a Graph()
  * Find shortest paths
  * Allow for extensions to give the spider new functionality.

## v0.6 Rexster Driver and Gremlin Server Driver (First Production Release, though unstable)
Use dependencies. Complete Driver.

## v0.7 Caching, Events, and Extensions
  * Caching interface to integrate into current caching scenario. Caching can be enabled or disabled.
   [Doctrine](https://packagist.org/packages/doctrine/cache) or [Laravel](https://packagist.org/packages/illuminate/cache) inspired
  * Extension for [Events](http://event.thephpleague.com/2.0/)
  * Extension for [Container](http://container.thephpleague.com/)
  
## v0.8 Finalize API and Security
  * Towards a production release
  * Security concerns, testing

## Beyond
  * Pathfinder and Utilities
  * Split drivers (except Gremlin) into first party packages
  * Drivers for MySQL, Orient, Mongo, etc

  