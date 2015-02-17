## Constraints on Relationships (Schema) and Properties
First kind of constraint is the one you described, lets call it Property Schema. That sets the schema for the properties on an element (i.e.. a node or a relationship). Using this you can index on a specific property, give labels to nodes, impose uniqueness constraint based on a particular property and can impose the soft constraints on the data elements that you put. like a Date property should be between 1-31. Neo4j currently doesn't support any constraint other than the uniqueness constraint. So these has to be imposed at the OGM level itself.

Another kind of constraint that is needed is a Structural Schema. This will impose a set of structural constraints on an element (i.e.. a node or a relationship). These sets of constraints will make sure that a element is following all the conditions when it gets connected to its neighborhood elements. In these set of constraints you can define constraints on relationships like: A relationship of type LIKES can connect a node with label USER to nodes with label BOOK or MUSIC or (FOOD and DRINK). Similarly you can put constraints on a node like: A node with label USER is required to be connected to atleast one another node with label USER using an outgoing relationship of type Friend.

You can express these kind of constraints using the cypher language MATCH patterns. I am currently working on the first phase of this OGM, which i am developing for node.js.

## Directions
When creating a relationship, no need to specify direction. The order says it all (alice "likes" bob)

## Path finder
Forked from [neo4j](https://github.com/jadell/neo4jphp/wiki/Paths)