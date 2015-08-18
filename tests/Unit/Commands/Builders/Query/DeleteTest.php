<?php
namespace Spider\Test\Unit\Commands\Builders\Query;

use Codeception\Specify;
use Spider\Commands\Bag;

class DeleteTest extends TestSetup
{
    use Specify;

    public function testDrop()
    {
        $this->specify("it drops a single record dispatching from `drop()`", function () {
            $actual = $this->builder
                ->drop(3); // dispatches command by itself

            $expected = $this->buildExpectedBag([
                'command' => Bag::COMMAND_DELETE,
                'target' => Bag::ELEMENT_VERTEX,
                'where'=>[[Bag::ELEMENT_ID, Bag::COMPARATOR_EQUAL, 3, Bag::CONJUNCTION_AND]]

            ]);

            $this->assertInstanceOf(
                "Spider\\Drivers\\Response",
                $actual,
                'failed to return correct command'
            );
            $this->assertEquals($expected, $this->builder->getBag(), 'failed to return correct script');
        });

        $this->specify("it drops a single record via `dispatch()`", function () {
            $actual = $this->builder
                ->drop()
                ->record(3)
                ->dispatch();

            $expected = $this->buildExpectedBag([
                'command' => Bag::COMMAND_DELETE,
                'target' => Bag::ELEMENT_VERTEX,
                'where'=>[[Bag::ELEMENT_ID, Bag::COMPARATOR_EQUAL, 3, Bag::CONJUNCTION_AND]]
            ]);

            $this->assertInstanceOf(
                "Spider\\Drivers\\Response",
                $actual,
                'failed to return correct command'
            );
            $this->assertEquals($expected, $this->builder->getBag(), 'failed to return correct script');
        });

        $this->specify("it drops multiple records dispatching from `drop()`", function () {
            $actual = $this->builder
                ->drop([1, 2, 3]);

            $expected = $this->buildExpectedBag([
                'command' => Bag::COMMAND_DELETE,
                'target' => Bag::ELEMENT_VERTEX,
                'where'=>[[Bag::ELEMENT_ID, Bag::COMPARATOR_IN, [1, 2, 3], Bag::CONJUNCTION_AND]]
            ]);

            $this->assertInstanceOf(
                "Spider\\Drivers\\Response",
                $actual,
                'failed to return correct command'
            );
            $this->assertEquals($expected, $this->builder->getBag(), 'failed to return correct script');
        });

        $this->specify("it drops multiple records dispatching from `dispatch()`", function () {
            $actual = $this->builder
                ->drop()
                ->records([1, 2, 3])
                ->dispatch();

            $expected = $this->buildExpectedBag([
                'command' => Bag::COMMAND_DELETE,
                'target' => Bag::ELEMENT_VERTEX,
                'where'=>[[Bag::ELEMENT_ID, Bag::COMPARATOR_IN, [1, 2, 3], Bag::CONJUNCTION_AND]]
            ]);

            $this->assertInstanceOf(
                "Spider\\Drivers\\Response",
                $actual,
                'failed to return correct command'
            );
            $this->assertEquals($expected, $this->builder->getBag(), 'failed to return correct script');
        });
    }
}
