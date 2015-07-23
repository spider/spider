<?php
namespace Spider\Test\Unit\Commands\Builder;

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
                'where' => [['username', Bag::COMPARATOR_EQUAL, 'chrismichaels84', 'AND']],
                'data' => ['name' => 'chris']
            ]);

            $this->assertEquals($expected, $actual, "failed to return correct command bag");
        });

        $this->specify("it updates multiple properties", function () {
            $actual = $this->builder
                ->update([
                    'name' => 'chris',
                    'birthday' => 'april'
                ])
                ->getCommandBag();

            $expected = $this->buildExpectedBag([
                'command' => Bag::COMMAND_UPDATE,
                'data' => ['name' => 'chris', 'birthday' => 'april'],
            ]);

            $this->assertEquals($expected, $actual, "failed to return correct command bag");
        });

        $this->specify("it updates multiple properties using data", function () {
            $data = [
                'name' => 'chris',
                'birthday' => 'april'
            ];

            $actual = $this->builder
                ->update()
                ->record(3)
                ->data($data) // alias withData()
                ->getCommandBag();

            $expected = $this->buildExpectedBag([
                'command' => Bag::COMMAND_UPDATE,
                'data' => ['name' => 'chris', 'birthday' => 'april'],
                'target' => new TargetID(3)
            ]);

            $this->assertEquals($expected, $actual, "failed to return correct command bag");
        });

        $this->specify("it updates a single record using updateFirst and data", function () {
            $actual = $this->builder
                ->updateFirst('users')
                ->where('username', 'chrismichaels84')
                ->data('name', 'chris')
                ->getCommandBag();

            $expected = $this->buildExpectedBag([
                'command' => Bag::COMMAND_UPDATE,
                'target' => 'users',
                'limit' => 1,
                'where' => [['username', Bag::COMPARATOR_EQUAL, 'chrismichaels84', 'AND']],
                'data' => ['name' => 'chris']
            ]);

            $this->assertEquals($expected, $actual, "failed to return correct command bag");
        });

        $this->specify("it updates multiple records via `all()`", function () {
            $data = [
                ['birth_month' => 'April'],
                ['two' => 2],
            ];

            $actual = $this->builder
                ->update('users')
                ->where('birth_month', 'apr')
                ->withData($data)
                ->all();

            $expected = $this->buildExpectedCommand([
                'command' => Bag::COMMAND_UPDATE,
                'target' => 'users',
                'limit' => false,
                'where' => [['birth_month', Bag::COMPARATOR_EQUAL, 'apr', 'AND']],
                'data' => $data
            ]);

            $this->assertEquals($expected, $actual->getScript(), "failed to return correct command bag");
        });
    }
}
