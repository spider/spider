<?php
namespace Spider\Test\Unit\Commands\Builders\Builder;

use Codeception\Specify;
use Spider\Commands\Bag;
use Spider\Test\Unit\Commands\Builders\Builder\TestSetup;

class CreateTest extends TestSetup
{
    use Specify;

    /* Create Tests */
    public function testCreateSugars()
    {
        $this->specify("it uses insert aliase", function () {
            $record = [
                'first' => 'first-value',
                'second' => 'second-value',
                Bag::ELEMENT_LABEL => 'target'
            ];

            $actual = $this->builder
                ->insert($record)
                ->getBag();

            $expected = $this->buildExpectedBag([
                'command' => Bag::COMMAND_CREATE,
                'target' => Bag::ELEMENT_VERTEX,
                'data' => [$record],
                'createCount' => 1
            ]);

            $this->assertEquals($expected, $actual, "failed to return correct command bag");
        });
    }
}
