<?php
namespace Spider\Test\Unit\Commands\Builders\Builder;

use Codeception\Specify;
use Spider\Commands\Bag;
use Spider\Graphs\ID as TargetID;

class UpdateTest extends TestSetup
{
    use Specify;

    /* Update Tests */
    public function testUpdateRecords()
    {
        $this->specify("it updates a single record with a single value by ID", function () {
            $actual = $this->builder
                ->update('name', 'chris')
                ->record(3)
                ->getCommandBag();

            $expected = $this->buildExpectedBag([
                'command' => Bag::COMMAND_UPDATE,
                'target' => new TargetID(3),
                'data' => ['name' => 'chris']
            ]);

            $this->assertEquals($expected, $actual, "failed to return correct command bag");
        });

        $this->specify("it updates a single record with a target and constraint", function () {
            $actual = $this->builder
                ->update('name', 'chris')
                ->where('username', 'chrismichaels84')
                ->from('users')
                ->limit(1)
                ->getCommandBag();

            $expected = $this->buildExpectedBag([
                'command' => Bag::COMMAND_UPDATE,
                'target' => 'users',
                'limit' => 1,
                'where' => [['username', Bag::COMPARATOR_EQUAL, 'chrismichaels84', Bag::CONJUNCTION_AND]],
                'data' => ['name' => 'chris']
            ]);

            $this->assertEquals($expected, $actual, "failed to return correct command bag");
        });

        $this->specify("it updates multiple records via `withData()`", function () {
            $data = [
                ['birth_month' => 'April'],
                ['two' => 2],
            ];

            $actual = $this->builder
                ->update('users')
                ->where('birth_month', 'apr')
                ->withData($data)
                ->all()
                ->getCommandBag();

            $expected = $this->buildExpectedBag([
                'command' => Bag::COMMAND_UPDATE,
                'target' => 'users',
                'limit' => false,
                'where' => [['birth_month', Bag::COMPARATOR_EQUAL, 'apr', Bag::CONJUNCTION_AND]],
                'data' => $data
            ]);

            $this->assertEquals($expected, $actual, "failed to return correct command bag");
        });
    }
}
