# Features and Milestones
This document breaks down the goals and workflows into feature sets with linear milestones, though no dates are given. Only semantic versions.

## v0.1 Manager and Connections
The connection manager creates and returns connections with drivers from a config array. 
  * `Connection\Manager` receives config array
    * `make()`s the default connection
    * `make()`s non-default connections
    * setters and getters
  * `Connection` holds credential information
    * works through a driver
    * has an interface

## v0.2 DriverInterface and Neo4j Driver
The Driver itself makes all the CRUD transactions. The driver is accessed through the connections.

## v0.3 Graphs and GraphCollections

## v0.4 GraphModels and OGM

## v0.5 Filtering and Security

## v0.6 Spider and Walker

## v0.7 Pathfinder and Utilities

## v0.8 Rexster Driver and Gremlin Server Driver

## v0.9 Caching, Events, and Extensions

## v1.0 Finalize API

## v1.0 Finalize API

  * Use [Laravel Manager](http://laravel.com/docs/5.0/extending#managers-and-factories) to create connections and drivers?
  * Simple one Class drivers
  * Populate Graphs from array data, not only data sources
  * Allow for user to getRawResult() from queries
  * Caching interface to integrate into current caching senario. Caching can be enabled or disabled
   [Doctrine](https://packagist.org/packages/doctrine/cache) or [Laravel](https://packagist.org/packages/illuminate/cache) inspired
  * Extension for [Events](http://event.thephpleague.com/2.0/)
  * Extension for [Container](http://container.thephpleague.com/)
  * Perhaps use Data Marshal [aura](https://github.com/auraphp/Aura.Marshal/tree/master)
  * Allow for multiple (batch) queries as sent through an array
  * Allow for PreparedQueries() to be saved and reused as templates
  * GraphModels protects against Mass Assignment
  * Allow for explicit models or generic (included) model ($manager->buildGraph()) or Spider\Models\Generic('type')
  * Use the [DataMapper Patter](http://martinfowler.com/eaaCatalog/dataMapper.html)
  