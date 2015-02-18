# Spider Graphs
A simple, flexible, and generic graph database abstraction for php. This is in proposal stage right now, the api is being worked out.
Pull Requests against the proposal documents are welcome, but only if you work with Graph Databases.

*Goals*
  * Framework agnostic, generic package.
  * Use drivers (with interfaces) to connect to specific graph databases (orient, neo4j, titat, etc)
  * Default to RexsterPro driver (included)
  * Include a basic OGM and GraphCollection to return Graph queries
  * Standard blueprints implementation
  * A filter builder that doesn't make your mind go nuts.
  * An easy transition from SQL or Mongo
  * Not limited to Graph Datastores, can make drivers for any datastore
  * Validation, Filtering, and Security [aura](https://github.com/auraphp/Aura.Filter)

*Inspired By*
  * [Eloquent ORM](http://laravel.com/docs/5.0/eloquent)
  * [Hibernate OGM](http://hibernate.org/ogm/)
  * Propel ORM
  * Neo4j Client
  * Bulbflow
  
  
