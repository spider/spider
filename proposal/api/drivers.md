```php
// Connect to and Manage Databases
$connection->connect();
$connection->listDatabases();
$connection->openDb($database);
$connection->function closeDb();

// Manage Vertices and Edges
$connection->addVertex($id, $properties);
$connection->addEdge($id, $from, $to, $properties);
$connection->updateVertex($id, $properties);
$connection->updateEdge($id, $properties);
$connection->getVertex($id);
$connection->getEdge($id);
$connection->dropVertex($id);
$connection->dropEdge($id);
```

| OrientDb      | Neo        | Spider       | Notes |
|---------------|------------|--------------|-------|
| connect       | (on create)  | connect      | |
| dbCreate      |  X        | createDb     | extract to trait |
| dbDrop        |  X       | dropDb       |extract to trait |
| dbExists      |  X       | dbExists     | extract to trait |
| dbList        |  X      | listDbs      | extract to trait |
| dbOpen        | (on create) | openDb       | extract to trait |
| dbSize        |  X       | sizeOfDb     | extract to trait |
| sendCommand         | new Query() | sendCommand        |
| sendCommand         | new Query()           | statement    |
| gremlin       | new Grm\Query() | X | make into a trait? |
| queryAsync    |  X       | ---         |
| recordLoad    |  getNode   | getVertex    |
| recordLoad    |  getRelationship | getEdge      |
| recordLoad    |  getRelationships          | **getVertexAndEdges** |
| recordCreate    | makeNode | addVertex    |
| recordCreate    | relateTo          | addEdge      |
| recordUpdate    | (modify, then save() | updateVertex    |
| recordUpdate    | (get, then save()          | updateEdge      |
| recordDelete    | (get, then delete() | dropVertex      |
| recordDelete    | (get, then delete() | dropEdge      |
| sqlBatch        | new Batch  |       |
| getTxStatement  | new Batch           | ---             |
| tx->begin       | new Batch           | ---              |
| tx->attach      | (statements)  | ---              |
| tx->commit      | batch->commit           | ---              |
| dataClusterDataRange | X   |               |
| dataClusterCount | X      |               |
| dbCountRecords | X        |               |
| dbReload      | X          |               |
| dataClusterAdd | X          |              |
| dataClusterDrop | X         |              |
| ?session tokens? |         |               |

 * In Neo, labels are similar to classes
