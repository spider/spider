# Spider Graphs
A simple, flexible, and generic graph database abstraction for php. This is in proposal stage right now, the api is being worked out.
Pull Requests against the proposal documents are welcome, but only if you work with Graph Databases.

*Goals*
  * Framework agnostic, generic package.
  * Use drivers (with interfaces) to connect to specific graph databases (orient, neo4j, titat, etc)
  * Default to RexsterPro driver (included)
  * Include a basic OGM and GraphCollection to return Graph queries
  * Execute Gramlin Queries
  * No querybuilder, but a seprate package that can enhance this one with a query builder
  * Standard blueprints implementation
  * A filter builder that doesn't make your mind go nuts.
  * An easy transition from SQL or Mongo
  * Simple one Class drivers
  * Not limited to Graph Datastores, can make drivers for any datastore
  * Populate Graphs from array data, not only data sources
  * Allow for user to getRawResult() from queries
  * Caching interface to integrate into current caching senario. Caching can be enabled or disabled
   [Doctrine](https://packagist.org/packages/doctrine/cache) or [Laravel](https://packagist.org/packages/illuminate/cache) inspired
  * Extension for [Events](http://event.thephpleague.com/2.0/)
  * Extension for [Container](http://container.thephpleague.com/)
  * Validation and Filtering [aura](https://github.com/auraphp/Aura.Filter)
  * Perhaps use Data Marshal [aura](https://github.com/auraphp/Aura.Marshal/tree/master)
  * Allow for multiple (batch) queries as sent through an array
  * Allow for PreparedQueries() to be saved and reused as templates
  * GraphModels protects against Mass Assignment
  * Allow for explicit models or generic (included) model ($manager->buildGraph()) or Spider\Models\Generic('type')
  * Use the [DataMapper Patter](http://martinfowler.com/eaaCatalog/dataMapper.html)
  
*Inspired By*
  * [Eloquent ORM](http://laravel.com/docs/5.0/eloquent)
  * [Hibernate OGM](http://hibernate.org/ogm/)
  
  
