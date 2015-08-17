<?php
namespace Spider\Test\Unit\Commands\Builders\Query;

use Codeception\Specify;
use Spider\Commands\Query;
use Spider\Connections\Connection;
use Spider\Test\Stubs\DriverStub as Driver;
use Spider\Test\Stubs\CommandProcessorStub as CommandProcessor;

/**
 * Class CommandBuilderTestSetup
 * @package Spider\Test\Unit\Commands
 */
class TestSetup extends \Spider\Test\Unit\Commands\Builders\TestSetup
{
    use Specify;

    public function setup()
    {
        $this->builder = new Query(new Connection(new Driver()), new CommandProcessor);
    }
}
