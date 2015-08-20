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
                'driver' => 'neo4j',
                'hostname' => 'localhost',
                'port' => 7474,
                'username' => "neo4j",
                'password' => "j4oen",
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
