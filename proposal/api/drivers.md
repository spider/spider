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

| OrientDb      | Neo        | Spider       |
|---------------|------------|--------------|
| connect       |            | connect      |
| dbCreate      |            | createDb     |
| dbDrop        |            | dropDb       |
| dbExists      |            | dbExists     |
| dbList        |            | listDbs      |
| dbOpen        |            | openDb       |
| dbSize        |            | sizeOfDb     |
| query         |            | query        |
| query         |            | statement    |
| queryAsync    |            | ---         |
| recordLoad    |            | getVertex    |
| recordLoad    |            | getEdge      |
| recordLoad    |            | getVertexAndEdges |
| recordCreate    |            | addVertex    |
| recordCreate    |            | addEdge      |
| recordUpdate    |            | updateVertex    |
| recordUpdate    |            | updateEdge      |
| recordDelete    |            | dropVertex      |
| recordDelete    |            | dropEdge      |
| sqlBatch        |            |       |
| getTxStatement  |            | ---             |
| tx->begin       |            | ---              |
| tx->attach      |            | ---              |
| tx->commit      |            | ---              |
| dataClusterDataRange |    |               |
| dataClusterCount |        |               |
| dbCountRecords |          |               |
| dbReload      |           |               |
| dataClusterAdd |           |              |
| dataClusterDrop |          |              |
| ?session tokens? |        |               |
