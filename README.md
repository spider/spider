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
