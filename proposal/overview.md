# Spider Proposal Overview
Spider is a graph-aware data manager with a driver-based abstraction for various graph datastores and a gremlin implementation.

## So What Is Spider, Really?
Let's break down the above description to describe the problem being solved.
  > A **graph**-aware **data manager** with a **driver-based** abstraction for various graph **datastores** and a **gremlin** implementation.

### Graphs Data Manager
Highly relational data can be thought of as a graph. Take the one below:

<a href="http://talks.chastell.net/rubyconf-2011/file/relations/graph-database.png"><img src="http://talks.chastell.net/rubyconf-2011/file/relations/graph-database.png" height="400" ></a>

We can immediately see a lot about this data. We can find who knows whom and for how long. If the graph were expanded, we could see who knows whom through others or how many friendships a single person has in common, etc.

Each person (a vertex) and each relationship (an edge) can hold properties, so you can add `last name` to people and `role` to is_members.

[Graph Databases](http://en.wikipedia.org/wiki/Graph_database) are [NoSQL](en.wikipedia.org/wiki/NoSQL) databases that specialize in this kind of data. But let's table tht for now.

Obviously, the possibilities of graphs can get uber complex, and super useful. For a more comprehensive introduction to graphs, check out
  * http://www.slideshare.net/maxdemarzi/introduction-to-graph-databases-12735789
  * http://en.wikipedia.org/wiki/Graph_database
  * http://neo4j.com/developer/graph-database/
  
Not worrying about *how* we get graph data, lets say you have some data that is highly relational, just like this graph. **Spider** is a set of tools that allow you to "walk", iterate over, and manipulate that graph.
It's goal is to be highly-intuitive, feature-full, and performant.

```php
/* A Simple Graph */
$graph = new Graph();
$michael = $graph->addVertex('Michael', ['location' => 'Houston']);
$nicole = $graph->addVertex('Nicole', ['location' => 'Houston']);
$graph->connect($michael, 'is_married_to', $nicole, ['since' => 2014]);

$wife = $graph->findOne('Michael', 'is_married_to');
```

### Driver Based Datastores
We want to persist that data, and there are lots of graph databases out there. Right now, especially for php, each has its own (usually not well maintained) language binding. **Spider** aims to be a generic abstraction for graph data regardless of which database you use.
In theory, you could even create a simple (one class) driver that works with a MySQL or file-based data store. You can switch datastores out, use remote connections, use multiple connections, and clustering. All from one api that is simple and flexible.

### Gremlin
[Tinkerpop](http://www.tinkerpop.com/) is an open-source organization that aims to standardize all these wonderful graph datastores.
**Spider** builds on this standardization, taking full advantage of the language and workflows that already work (why re-invent the wheel?)
Even cooler, Tinkerpop has created a Server API with its own, flexible sendCommand language that sits on top of nearly all graph databases. This allows you to use gremlin with [OrientDB](http://www.orientechnologies.com/), [Neo4j](http://neo4j.com), etc.
Spider includes a driver to connect to these databases, so in 90 percent of the cases, all you have to do is `composer require` Spider and off you go :)

## The Proposal
Spider is only in the proposal stages, right now. We are working out the goals, architecture, and API over the next few weeks before diving into the actionalble code. Better to measure twice and cut once.
If you have any feedback, please open an issue and label `proposal` for discussion. You can also edit these markdown files and submit pull requests. Be clear about what you are trying to do.
This is a living proposal. The more feedback the better.

### Overreaching Goals
  * Framework agnostic, generic package using composer.
  * Community-driven, best practices code (DRY, SOLID, PHP The Right Way, PSRs, Tinkerpop, Testing, etc.)
  * An easy transition from SQL or Mongo
  * Simple, fluent, and consistent API
  * An Object-Graph-Mapper and Models inspired by Eloquent, Propel, and Monga.
  * A filter/sendCommand builder that doesn't make your mind go nuts.
  * Simple drivers to connect to specific graph databases (orient, neo4j, titat, etc) or other datastores
  * Handle multiple connections
  * Validation, filtering, security, and performance.
  * Extensible and configurable (e.g. for caching).
  
### Table of Contents
  * **[User Story](user-story.md)**: Like an acceptence test, a developer uses v1.0 in the future.
  * **[Features and Milestones](features-milestones.md)**: An organic roadmap for development. Which features land in which semver versions up to 1.0. Great for an overview of all features.
  * **[Architecture](architecture.md)**: Notes on construction, coding, and design. Dependencies.
  * **[Inspiration](inspiration.md)**: Bits of code from other packages and languages we like. A hodge podge.
  * **[Quotes](quotes.md)**: Snippets of quotes about graphs that may be useful.
  * **Api**: Fleshing out the *public* api for the different components.
    * [Connections, Managers, and Drivers](api/connections-drivers.md)
    * [Graphs and Models](api/graphs-models.md)
    * [QueryBuilder](api/sendCommand-builder.md)
    * [Spider and Utilities](api/spider.md)
  * **[Meta](meta.md)**: Other thoughts.

