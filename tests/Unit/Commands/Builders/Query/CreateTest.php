<?php
namespace Spider\Test\Unit\Commands\Builders\Query;

use Codeception\Specify;
use Spider\Commands\Bag;

class CreateTest extends TestSetup
{
    use Specify;

    /* Create Tests */
    public function testDispatchOnInsert()
    {
        $this->specify("it inserts a single record", function () {
            $record = [
                'first' => 'first-value',
                'second' => 'second-value'
            ];

            $actual = $this->builder
                ->into('target')
                ->insert($record);

            $expected = $this->buildExpectedBag([
                'command' => Bag::COMMAND_CREATE,
                'target' => Bag::ELEMENT_VERTEX,
                'where'=>[[Bag::ELEMENT_LABEL, Bag::COMPARATOR_EQUAL, "target", Bag::CONJUNCTION_AND]],
                'data' => [$record],
                'createCount' => 1
            ]);

            $this->assertEquals($expected, $this->builder->getBag(), 'failed to return correct script');
            $this->assertInstanceOf("\\Spider\\Drivers\\Response", $actual, 'failed to get a db response');
        });

        $this->specify("it inserts multiple records", function () {
            $records = [
                ['first' => 'first-value', 'A', 'a'],
                ['first' => 'second-value', 'B', 'b']
            ];

            $actual = $this->builder
                ->into('target')
                ->insert($records);

            $expected = $this->buildExpectedBag([
                'command' => Bag::COMMAND_CREATE,
                'target' => Bag::ELEMENT_VERTEX,
                'where'=>[[Bag::ELEMENT_LABEL, Bag::COMPARATOR_EQUAL, "target", Bag::CONJUNCTION_AND]],
                'data' => $records,
                'createCount' => 2
            ]);

            $this->assertInstanceOf(
                "\\Spider\\Drivers\\Response",
                $actual,
                'failed to return correct response'
            );
            $this->assertEquals($expected, $this->builder->getBag(), 'failed to return correct script');
        });
    }
}
