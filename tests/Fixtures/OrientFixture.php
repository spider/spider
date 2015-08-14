<?php
namespace Spider\Test\Fixtures;
use PhpOrient\PhpOrient;

/**
 * Class OrientFixture
 * @package Spider\Test\Fixtures
 */
class OrientFixture extends DbFixture
{
    public function load()
    {
        $client = new PhpOrient();
        $client->configure([
            'hostname' => 'localhost',
            'port' => 2424,
            'username' => 'root',
            'password' => "root",
        ]);
        $client->connect();

        if ($client->dbExists('modern_graph')) {
            throw new \Exception("Cannot create Orient database fixture. `modern_graph` already exists");
        }

        $client->dbCreate(
            'modern_graph',
            PhpOrient::STORAGE_TYPE_MEMORY,
            PhpOrient::DATABASE_TYPE_GRAPH
        );

        $client->dbOpen('modern_graph', 'root', 'root');

        $client->command('create class person extends V');
        $client->command('create class knows extends E');
        $client->command('create class created extends E');

        $client->sqlBatch(
            'begin;
            let a = INSERT INTO person CONTENT {name:"marko",age:29 } RETURN @rid;
            let b = INSERT INTO person CONTENT {name:"vadas",age:27 } RETURN @rid;
            let c = INSERT INTO person CONTENT {name:"peter",age:35 } RETURN @rid;
            let d = INSERT INTO person CONTENT {name:"josh",age:32 } RETURN @rid;
            let e = INSERT INTO person CONTENT {name:"lop",lang:"java" } RETURN @rid;
            let f = INSERT INTO person CONTENT {name:"ripple",lang:"java" } RETURN @rid;

            CREATE EDGE knows FROM $a TO $b CONTENT { "weight" : 0.5 };
            CREATE EDGE created FROM $a TO $e CONTENT { "weight" : 0.4 };
            CREATE EDGE created FROM $c TO $e CONTENT { "weight" : 0.2 };
            CREATE EDGE created FROM $d TO $e CONTENT { "weight" : 0.4 };
            CREATE EDGE created FROM $d TO $f CONTENT { "weight" : 1.0 };

            commit retry 100;
            return a;'
        );

        $client->dbClose();

        return $this;
    }

    public function unload()
    {
        $client = new PhpOrient();
        $client->configure([
            'hostname' => 'localhost',
            'port' => 2424,
            'username' => 'root',
            'password' => "root",
            'database' => 'modern_graph',
        ]);
        $client->connect();

        if ($client->dbExists('modern_graph')) {
            $client->dbDrop('modern_graph');
        }
    }

    public function setDependencies()
    {
        // nothing
    }

    public function getDependencies()
    {
        // Nothing
    }
}
