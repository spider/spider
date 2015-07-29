<?php
namespace Spider\Test\Unit\Commands\Builders\Query;

use Codeception\Specify;
use Spider\Commands\Bag;
use Spider\Commands\Builder;
use Spider\Commands\Query;
use Spider\Test\Stubs\CommandProcessorStub;
use Spider\Test\Stubs\ConnectionStub;

/**
 * Class CommandBuilderTestSetup
 * @package Spider\Test\Unit\Commands
 */
class TestSetup extends \Spider\Test\Unit\Commands\Builders\TestSetup
{
    use Specify;

    public function setup()
    {
        $this->builder = new Query(new CommandProcessorStub(), new ConnectionStub());
    }
}
