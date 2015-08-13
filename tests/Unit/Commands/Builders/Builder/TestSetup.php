<?php
namespace Spider\Test\Unit\Commands\Builders\Builder;

use Codeception\Specify;
use Spider\Commands\Builder;

/**
 * Class CommandBuilderTestSetup
 * @package Spider\Test\Unit\Commands
 */
class TestSetup extends \Spider\Test\Unit\Commands\Builders\TestSetup
{
    use Specify;

    public function setup()
    {
        $this->builder = new Builder();
    }
}
