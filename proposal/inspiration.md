## Models and OGM

Create, update and refresh against a datastore
```php
// From [neomodel](http://neomodel.readthedocs.org/en/latest/getting_started.html)
$jim = Person(name='Jim', age=3).save() // Saves to datastore
$jim.age = 4
$jim.save() // validation happens here, and saves to datastore
$jim.age = 2
$jim.refresh() // reload properties from datastore. Now, Jim's age is 4
```

Create and manage relationships through classes
  * I like using classes
  * I like relate(node, "relationship", node), but need direction and properties
  * I like loadRelated() to expand the graph
  
```python
class Person(object):
    def __init__(self, email=None, name=None, age=None):
        self.email = email
        self.name = name
        self.age = age
        
alice = Person("alice@example.com", "Alice", 34)
connection.save_unique("People", "email", alice.email, alice)

bob = Person("bob@example.org", "Bob", 66)
carol = Person("carol@example.net", "Carol", 42)

connection.relate(alice, "LIKES", bob)     # these relationships are not saved
connection.relate(alice, "LIKES", carol)   # until `alice` is saved

connection.save(alice)

friends = store.load_related(alice, "LIKES", Person)
print("Alice likes {0}".format(" and ".join(str(f) for f in friends)))
```

Query Relationships
  * I like the has()
```python
Coffee.nodes.has(suppliers=True)
```

Create using Graph()
```python
graph = Graph()
alice = Node("Person", name="Alice")
graph.create(alice)

# Now Alice speaks german
german, speaks = graph.create({"name": "German"}, (alice, "SPEAKS", 0))
```

Creating a Vertex (from https://github.com/jadell/neo4jphp/wiki/Nodes-and-Relationships)
```php
$arthur = $connection->makeNode();
$arthur->setProperty('name', 'Arthur Dent')
    ->setProperty('mood', 'nervous')
    ->setProperty('home', 'small cottage')
    ->save();

$ford = $client->makeNode();
$ford->setProperty('name', 'Ford Prefect')
    ->setProperty('occupation', 'travel writer')
    ->save();

$arthurId = $arthur->getId();

// Retrieve and modify node by id
$character = $client->getNode($arthurId);

foreach ($character->getProperties() as $key => $value) {
    echo "$key: $value\n";
}
// prints:
//   name: Arthur Dent
//   mood: nervous
//   home: small cottage

$character->removeProperty('mood')
    ->setProperty('home', 'demolished')
    ->save();

foreach ($character->getProperties() as $key => $value) {
    echo "$key: $value\n";
}
// prints:
//   name: Arthur Dent
//   home: demolished

// Delete
$earth = $client->getNode(123);
$earth->delete();
```

Another way to work with nodes and relationships (https://github.com/neoxygen/neo4j-neoclient)
  * I like getRelationships() and getSingleRelationship()
  * I like getting multiple properties
```php
// Working with the relationships

$movie = $result->getSingleNode('Movie');
$actors = $movie->getRelationships('ACTS_IN');
// Or you may want to specify direction
$actors = $movie->getRelationships('ACTS_IN', 'IN');

// If you need only one relationship :
$actor = $movie->getSingleRelationship('ACTS_IN');

// Getting node/relationships properties

// Getting one property
$actor = $result->getSingleNode('Actor');
$name = $actor->getProperty('name');

// Getting all properties
$props = $actor->getProperties();

// Getting a set of properties
$props = $actor->getProperties(['name', 'date_of_birh']);
```