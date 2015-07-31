<?php
namespace Spider\Test\Unit\Commands\Builders\BaseBuilder;

use Codeception\Specify;
use Spider\Commands\Bag;
use Spider\Test\Unit\Commands\Builders\TestSetup;

class UpdateTest extends TestSetup
{
    use Specify;

    /* Update Tests */
    public function testUpdateRecords()
    {
        $this->specify("it updates a single record with a single value by ID", function () {
            $actual = $this->builder
                ->update('name', 'chris')
                ->getCommandBag();

            $expected = $this->buildExpectedBag([
                'command' => Bag::COMMAND_UPDATE,
                'data' => ['name' => 'chris']
            ]);

            $this->assertEquals($expected, $actual, "failed to return correct command bag");
        });

        $this->specify("it updates a single record with a target and constraint", function () {
            $actual = $this->builder
                ->update('name', 'chris')
                ->where('username', 'chrismichaels84')
                ->target('users')
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
                ->target('target')
                ->data($data) // alias withData()
                ->getCommandBag();

            $expected = $this->buildExpectedBag([
                'command' => Bag::COMMAND_UPDATE,
                'data' => ['name' => 'chris', 'birthday' => 'april'],
                'target' => 'target'
            ]);

            $this->assertEquals($expected, $actual, "failed to return correct command bag");
        });
    }
}
