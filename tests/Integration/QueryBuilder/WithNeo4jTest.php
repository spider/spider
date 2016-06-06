<?php
namespace Spider\Test\Integration\QueryBuilder;

use Codeception\Specify;
use Spider\Commands\Command;
use Spider\Commands\Query;
use Spider\Connections\Manager;
use Spider\Test\Fixtures\Graph;
use Spider\Test\Fixtures\NeoFixture;
use Spider\Test\Fixtures\OrientFixture;

class WithNeo4jTest extends BaseTestSuite
{
    public function setup()
    {
        $this->beforeSpecify(function () {
            $this->fixture = new NeoFixture();
            $this->fixture->unload();
            $this->fixture->load();
        });

        $manager = new Manager([
            'default' => 'neo',
            'neo' => [
                'hostname' => getenv('NEO4J_HOSTNAME'),
                'port' => getenv('NEO4J_PORT'),
                'username' => getenv('NEO4J_USERNAME'),
                'password' => getenv('NEO4J_PASSWORD'),
                'database' => 'modern_graph',
                'driver' => 'neo4j'
            ]
        ]);

        $this->query = new Query($manager->make());
    }

    public function teardown()
    {
        $this->afterSpecify(function () {
            $this->fixture->unload();
        });
    }

    public function testDrop()
    {
        $this->markTestSkipped('Cypher processor needs traversal logic before being able to drop.');
    }

//    public function testDrop()
//    {
//        $this->assertTrue(true);
//    }
//
//    public function testUpdates()
//    {
//        $this->assertTrue(true);
//    }
}
