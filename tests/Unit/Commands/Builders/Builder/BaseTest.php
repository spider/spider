<?php
namespace Spider\Test\Unit\Commands\Builders\Builder;

use Codeception\Specify;
use Spider\Commands\Bag;
use Spider\Graphs\ID as TargetID;
use Spider\Test\Stubs\CommandProcessorStub;
use Spider\Test\Unit\Commands\Builders\Builder\TestSetup;

class BaseTest extends TestSetup
{
    use Specify;

    /* Record(s) target Tests */
    public function testRecordTargets()
    {
        $this->specify("it adds a single record id via `record()`", function () {
            $actual = $this->builder
                ->record(3)
                ->getCommandBag();

            $expected = $this->buildExpectedBag([
                'target' => new TargetID(3)
            ]);

            $this->assertEquals($expected, $actual, "failed to return correct command bag");
        });

        $this->specify("it adds multiple records via ids", function () {
            $actual = $this->builder
                ->records([1, 2, 3])
                ->getCommandBag();

            $expected = $this->buildExpectedBag([
                'target' => [
                    new TargetID(1),
                    new TargetID(2),
                    new TargetID(3),
                ],
            ]);

            $this->assertEquals($expected, $actual, "failed to return correct command bag");
        });
    }

    public function testBuildScripts()
    {
        $this->specify("sets optional language processors", function () {
            $this->builder->setProcessor(new CommandProcessorStub());

            $actual = $this->builder
                ->retrieve('something')
                ->target('target')
                ->getScript();

            $expected = $this->buildExpectedCommand([
                'command' => Bag::COMMAND_RETRIEVE,
                'target' => 'target',
                'projections' => ['something']
            ]);

            $this->assertEquals($expected, $actual, "failed to return correct command bag");
        });
    }

    public function testResponseFormats()
    {
        $this->specify("sets response format as tree", function () {
            $this->builder->setProcessor(new CommandProcessorStub());

            $actual = $this->builder
                ->tree()
                ->getCommandBag();

            $expected = $this->buildExpectedBag([
                'format' => Bag::FORMAT_TREE
            ]);

            $this->assertEquals($expected, $actual, "failed to return correct command bag");
        });

        $this->specify("sets response format as path", function () {
            $this->builder->setProcessor(new CommandProcessorStub());

            $actual = $this->builder
                ->path()
                ->getCommandBag();

            $expected = $this->buildExpectedBag([
                'format' => Bag::FORMAT_PATH
            ]);

            $this->assertEquals($expected, $actual, "failed to return correct command bag");
        });
    }
}
