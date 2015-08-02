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

            $expected = $this->buildExpectedCommand([
                'command' => Bag::COMMAND_CREATE,
                'target' => "target",
                'data' => $record,
                'createCount' => 1
            ]);

            $this->assertInstanceOf(
                "Spider\\Drivers\\Response",
                $actual,
                'failed to return correct command'
            );
            $this->assertEquals($expected, $actual->getRaw()->getScript(), 'failed to return correct script');
        });

        $this->specify("it inserts multiple records", function () {
            $records = [
                ['first' => 'first-value', 'A', 'a'],
                ['first' => 'second-value', 'B', 'b']
            ];

            $actual = $this->builder
                ->into('target')
                ->insert($records);

            $expected = $this->buildExpectedCommand([
                'command' => Bag::COMMAND_CREATE,
                'target' => "target",
                'data' => $records,
                'createCount' => 2
            ]);

            $this->assertInstanceOf(
                "Spider\\Drivers\\Response",
                $actual,
                'failed to return correct command'
            );
            $this->assertEquals($expected, $actual->getRaw()->getScript(), 'failed to return correct script');
        });
    }
}