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

//    public function testDirectCommands()
//    {
//        $this->specify("it issues a direct command via `Command`", function () {
//            $command = new Command("SELECT FROM V", 'orientSQL');
//            $response = $this->query
//                ->command($command);
//
//            $expected = $this->expected;
//
//            $this->assertInstanceOf('Spider\Drivers\Response', $response, "failed to return a `Response`");
//            $consistent = $response->getSet();
//
//            $this->assertTrue(is_array($consistent), 'failed to return an array');
//            $this->assertCount(count($expected), $consistent, 'failed to return the correct number of records');
//            $this->assertInstanceOf('Spider\Base\Collection', $consistent[0], 'failed to return an array of collections');
//            $this->assertEquals($expected[0]['name'], $consistent[0]->name, 'failed to return correct first collection');
//        });

//        $this->specify("it issues a direct command via a string", function () {
//            $response = $this->query
//                ->command("SELECT FROM V");
//
//            $expected = $this->expected;
//
//            $this->assertInstanceOf('Spider\Drivers\Response', $response, "failed to return a `Response`");
//            $consistent = $response->getSet();
//
//            $this->assertTrue(is_array($consistent), 'failed to return an array');
//            $this->assertCount(count($expected), $consistent, 'failed to return the correct number of records');
//            $this->assertInstanceOf('Spider\Base\Collection', $consistent[0], 'failed to return an array of collections');
//            $this->assertEquals($expected[0]['name'], $consistent[0]->name, 'failed to return correct first collection');
//        });
//    }
}
