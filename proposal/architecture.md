# Proposed Architecture Notes
In an effort to be modular, DRY, and SOLID, the package itself is divided into the following, self-contained pieces, each with their own namespace:
  * **Spider\Spider**: Top level API and actual Spider that walks and manipulates graphs
  * **Spider\Walker**: Gremlin-like sendCommand builder "walks" Graph data
  * **Spider\Pathfinder**: Forked from [neo4j](https://github.com/jadell/neo4jphp/wiki/Paths)
  * **Spider\QueryBuilder**: Build queries from chained method calls. (May be part of Walker)
  * **Spider\Graph**: Individual graph from sendCommand results or population. Basically an OGM and Graph Collection.
  * **Spider\Connection**: Manages various connections and interfaces. Connects to database through Drivers
  * **Spider\Drivers**: First party drivers for Tinkerpop and maybe SQL
  * **Spider\Algorithms**: Command pack for [midas](http://github.com/chrismichaels84/midas)
  * **<vendor>\<DataStore>Driver**: ThirdParty drivers from various datastores
 
## Algorithms
  * Much of the data manipulation should be reusable algorithms. There should also exist algorithms for finding shortest path, etc.
  * Depend on [midas](http://github.com/chrismichaels84/midas) for algorithm management
  
## Sample Api Usage
```php
$driver = new Orientdb\OrientDriver();
$connection = new Spider\Connection\Connection($driver, $settings);
$graph = new Graph('graph_name', $connection);

$graph->walk()
  ->start(['name'] => 'Michael'])
  ->out('friends_with')
  ->go()
  
$graph->michael->friends_with->nicole->setProperty('last_name', 'wilson');
$graph->save();
```
  
## Connections and Drivers
Connections hold the actual connection and credentials, but do all work through the Driver

### Connection Manager
Spider\Connection\Manager() forges and manages connections from config data. Great for ServiceProviders.

```php
$config = [
    'driver' => 'orientdb' // Default driver
    'drivers' => [
        'orientdb' => 'OrientDb\OrientDriver2'
        'rexster' => 'Spider\Drivers\Rexster\ResterProDriver'
    ],
    
    'connection' => 'my_connection',
    'connections' => [
        'my_connection' => [
            'driver' => 'orientdb',
            'url' => 'whatever.com',
            'other' => 'settings'
        ],
        'another_connection' => [
            'driver' => 'Vendor\Another\Driver',
            'other' => 'settings'
        ],
    ],
];

$manager = new Spider\Connection\Manager($config);
$connection = $manager->make(); // sends out default connection
$connection = $manager->make('another_connection');
```

### Connection
  * Spider\Connection\Connection() holds the actual connection credentials
  * Works through a driver, passing almost everything down.
  
```php
$connection = new Spider\Connection\Connection(new Vendor\Driver, $settingsNeededByDriver);

$connection->getDriver()
$connection->setDriver($driver);
$connection->__settings('value');

$connection->addVertex('name', ['properties']); // passed directly to driver
//etc
```

### Drivers
  * A driver is a single class (with possible dependencies) that connects to, and executes CRUD opperations on, a datastore.
  * Must implement Spider\Connections\DriverInterface
  * Can have other datastore-specific actions
  
```php
$driver = new Driver();
$driver->create();
$driver->insert();
$driver->update();
$driver->drop();
$driver->gremlin() // if supported
$driver->addVertex()
$driver->addEdge()
//etc
```

Use the driver's additional features (say for Orient)

```php
$driver = new Orient\Driver();
$connection = new Spider\Connection\Connection($driver, $settings);
$graph = new Spider\Graph\Graph('graph_name', $driver);
$graph->driver->createDocument() // for example
```

#### Driver Recommended Structure
  * `Driver\Driver`: required class that implements DriverInterface
  * `Driver\Connector`: Establishes connection
  * `Diver\Translator`: Translates returns to Spider\Graphs
  * `Driver\Operator`: Builds communication

## Graphs
There are two basic types of graphs
  * Those connected to a data store
  * Those not connected to a datastore

### Notes
  * If the Graph has a `$connection` to a datastore, it can repopulate itself from queries.
  * If the Graph does not have a `$connection`, it is just a representation of relational data
  * All queries and walks return Graphs with or without connections
  * Graphs can create `Spider\Walker\Walker()`s to traverse the data already stored in it, or to gather new data from connection

### Object Graph Mapper (Model)
`Spider\Graph\Model` includes basic model methods like

```php
$graph = new Spider\Graph\Model('graph_name', $connection);
$graph->find();
$graph->findOne();
$graph->walk();
$graph->sendCommand()
$graph->save()
//etc
```

## Walker
`Spider\Walker\Walker` is an object that can walk data. Usually, it is produced a `walk()` method on a Graph or the Spider.

```php
$walker = new Walker($graph);
$walker->start('here')->out('knows')->go();

if ( $walker->graph->connection ) {
    $walker->retrieve()->start('here')->out('knows')->go();
}
```

## Spider
The `Spider\Spider\Spider` (don't like this naming) is the top level api. It wraps all else, and is the recommended way of working.

```php
$manger = new Spider\Connection\Manger($config);
$spider = new Spider( $manager->make() );
$spider->graph('name')
$spider->walk()->start()->out()->go()
$spider->find();
$spider->findOne();
// etc
```


  
