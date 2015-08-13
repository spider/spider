<?php
namespace Spider\Test\Integration\QueryBuilder;

use Codeception\Specify;
use Spider\Commands\Query;
use Spider\Connections\Manager;
use Spider\Test\Fixtures\Graph;

class WithOrientTest extends BaseTestSuite
{
    protected $query;

    public function setup()
    {
        $manager = new Manager([
            'default' => 'orient',
            'orient' => Graph::$servers['orient']
        ]);

        $this->query = new Query($manager->make());
    }
}
