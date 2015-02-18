# Spider Proposal Overview
Spider is a graph-aware data manager with a driver-based abstraction for various graph datastores and a blueprints implementation.

## So What Is Spider, Really?
Let's break down the above description to describe the problem being solved.
  > A **graph**-aware **data manager** with a **driver-based abstraction** for various graph **datastores** and a **blueprints** implementation.

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

### Data Manager


