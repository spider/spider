<?php
namespace Spider\Test\Integration\QueryBuilder;

use Codeception\Specify;
use Spider\Commands\Query;
use Spider\Connections\Manager;
use Spider\Test\Fixtures\Graph;
use Spider\Test\Fixtures\OrientFixture;

class WithOrientTest extends BaseTestSuite
{
    public function setup()
    {
        $this->fixture = new OrientFixture();
        $this->fixture->unload();
        $this->fixture->load();

        $manager = new Manager([
            'default' => 'orient',
            'orient' => [
                'hostname' => 'localhost',
                'port' => 2424,
                'username' => 'root',
                'password' => "root",
                'database' => 'modern_graph',
                'driver' => 'orientdb'
            ]
        ]);

        $this->query = new Query($manager->make());
    }

    public function teardown()
    {
        $this->fixture->unload();
    }
}
