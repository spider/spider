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
        $this->fixture = (new OrientFixture())->load();
        $this->expected = (array)$this->fixture->getData();

        $manager = new Manager([
            'default' => 'orient',
            'orient' => Graph::$servers['orient']
        ]);

        $this->query = new Query($manager->make());
    }

    public function teardown()
    {
        $this->fixture->unload();
    }
}
