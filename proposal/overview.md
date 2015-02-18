# Spider Proposal Overview
Spider is a graph-aware data manager with a driver-based abstraction for various graph datastores and a blueprints implementation.

## So What Is Spider, Really?
Let's break down the above description to describe the problem being solved.
  > A **graph**-aware **data manager** with a **driver-based** abstraction for various graph **datastores** and a **gremlin** implementation.

### Graphs Data Manager
Highly relational data can be thought of as a graph. Take the one below:

![alt text](http://talks.chastell.net/rubyconf-2011/file/relations/graph-database.png "Property Graph")

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
Even cooler, Tinkerpop has created a Server API with its own, flexible query language that sits on top of nearly all graph databases. This allows you to use gremlin with [OrientDB](http://www.orientechnologies.com/), [Neo4j](http://neo4j.com), etc.
Spider includes a driver to connect to these databases, so in 90 percent of the cases, all you have to do is `composer require` Spider and off you go :)



