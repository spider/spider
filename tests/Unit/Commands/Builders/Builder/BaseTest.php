<?php
namespace Spider\Test\Unit\Commands\Builders\Builder;

use Codeception\Specify;
use Spider\Commands\Bag;
use Spider\Test\Stubs\CommandProcessorStub;
use Spider\Test\Unit\Commands\Builders\Builder\TestSetup;

/* BaseBuilder tested elsewhere */
class BaseTest extends TestSetup
{
    use Specify;

    /* Record(s) target Tests */
    public function testRecordTargets()
    {
        $this->specify("it adds a single record id via `record()`", function () {
            $actual = $this->builder
                ->record(3)
                ->getBag();

            $expected = $this->buildExpectedBag([
                'where' => [[Bag::ELEMENT_ID, Bag::COMPARATOR_EQUAL, 3, Bag::CONJUNCTION_AND]]
            ]);

            $this->assertEquals($expected, $actual, "failed to return correct command bag");
        });

        $this->specify("it adds multiple records via ids", function () {
            $actual = $this->builder
                ->records([1, 2, 3])
                ->getBag();

            $expected = $this->buildExpectedBag([
                'where' => [[Bag::ELEMENT_ID, Bag::COMPARATOR_IN, [1, 2, 3], Bag::CONJUNCTION_AND]]
            ]);

            $this->assertEquals($expected, $actual, "failed to return correct command bag");
        });
    }

    public function testGetScript()
    {
        $this->specify("sets optional language processors", function () {
            $this->builder->setProcessor(new CommandProcessorStub());

            $actual = $this->builder
                ->retrieve('something')
                ->label('target')
                ->getScript();

            $expected = $this->buildExpectedCommand([
                'command' => Bag::COMMAND_RETRIEVE,
                'target' => Bag::ELEMENT_VERTEX,
                'projections' => ['something'],
                'where' => [[Bag::ELEMENT_LABEL, Bag::COMPARATOR_EQUAL, 'target', Bag::CONJUNCTION_AND]]
            ]);

            $this->assertEquals($expected, $actual, "failed to return correct command bag");
        });
    }

    // Does no dispatching, only flags the bag
    public function testResponseFormats()
    {
        $this->specify("sets response format as tree", function () {
            $this->builder->setProcessor(new CommandProcessorStub());

            $actual = $this->builder
                ->tree()
                ->getBag();

            $expected = $this->buildExpectedBag([
                'map' => Bag::MAP_TREE
            ]);

            $this->assertEquals($expected, $actual, "failed to return correct command bag");
        });

        $this->specify("sets response format as path", function () {
            $this->builder->setProcessor(new CommandProcessorStub());

            $actual = $this->builder
                ->path()
                ->getBag();

            $expected = $this->buildExpectedBag([
                 'map' => Bag::MAP_PATH
            ]);

            $this->assertEquals($expected, $actual, "failed to return correct command bag");
        });
    }
}
