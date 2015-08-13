<?php
namespace Spider\Test\Fixtures;

use PhpOrient\PhpOrient;

class Orient extends Fixture
{
    protected $client;

    public function setup()
    {
        $this->client = new PhpOrient();
        $this->client->configure([
            'username' => 'root',
            'password' => 'root',
            'hostname' => 'localhost',
            'port' => 2424,
        ]);
        $this->client->connect();

        if ($this->client->dbExists('spider_test_graph')) {
            throw new \Exception("Cannot create Orient database fixture. `spider_test_graph` already exists");
        }

        $this->client->dbCreate(
            'spider_test_graph',
            PhpOrient::STORAGE_TYPE_MEMORY,
            PhpOrient::DATABASE_TYPE_GRAPH
        );

        $this->client->dbOpen('spider_test_graph', 'root', 'root');

        $this->client->command('create class person extends V');
        $this->client->command('create class knows extends E');
        $this->client->command('create class created extends E');

        $this->client->sqlBatch(
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

        $this->client->dbClose();
    }

    public function teardown()
    {
        $this->client = new PhpOrient();
        $this->client->configure([
            'username' => 'root',
            'password' => 'root',
            'hostname' => 'localhost',
            'port' => 2424,
        ]);
        $this->client->connect();

        if ($this->client->dbExists('spider_test_graph')) {
            $this->client->dbDrop('spider_test_graph');
        }
    }

    public function reset()
    {
        $this->teardown();
        $this->setup();
    }
}
