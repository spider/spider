<?php
namespace Spider\Test\Unit\Commands\Builder;

use Codeception\Specify;
use Spider\Commands\Bag;

class CreateTest extends TestSetup
{
    use Specify;

    /* Create Tests */
    public function testCreateRecords()
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

            $this->assertEquals($expected, $actual->getScript(), "failed to return correct command bag");
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

            $this->assertEquals($expected, $actual->getScript(), "failed to return correct command bag");
        });
    }
}
