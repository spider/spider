# graphs
A simple, flexible, and generic graph database abstraction for php

*Goals*
  * Framework agnostic, generic package.
  * Use drivers (with interfaces) to connect to specific graph databases (orient, neo4j, titat, etc)
  * Default to RexsterPro driver (included)
  * Include a basic OGM and GraphCollection to return Graph queries
  * Execute Gramlin Queries
  * No querybuilder, but a seprate package that can enhance this one with a query builder
  * Standard blueprints implementation
  * A filter builder that doesn't make your mind go nuts.

*Example API*
```php
$manager = new Manager($cfg);
$graph = $manager->newGraph('name');
$graph->addVertex('first', 'second', ['parameters' => 'here']);
$graph->save()

```
