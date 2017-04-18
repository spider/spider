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
                ->update(['name'=> 'chris'])
                ->getBag();

            $expected = $this->buildExpectedBag([
                'command' => Bag::COMMAND_UPDATE,
                'data' => [['name' => 'chris']]
            ]);

            $this->assertEquals($expected, $actual, "failed to return correct command bag");
        });

        $this->specify("it updates a single record with a target and constraint", function () {
            $actual = $this->builder
                ->update(['name'=> 'chris'])
                ->internalWhere(['username', Bag::COMPARATOR_EQUAL, 'chrismichaels84', Bag::CONJUNCTION_AND])
                ->internalWhere([Bag::ELEMENT_LABEL, Bag::COMPARATOR_EQUAL, 'target', Bag::CONJUNCTION_AND])
                ->type(Bag::ELEMENT_VERTEX)
                ->limit(1)
                ->getBag();

            $expected = $this->buildExpectedBag([
                'command' => Bag::COMMAND_UPDATE,
                'target' => Bag::ELEMENT_VERTEX,
                'limit' => 1,
                'where' => [['username', Bag::COMPARATOR_EQUAL, 'chrismichaels84', Bag::CONJUNCTION_AND],[Bag::ELEMENT_LABEL, Bag::COMPARATOR_EQUAL, 'target', Bag::CONJUNCTION_AND]],
                'data' => [['name' => 'chris']]
            ]);

            $this->assertEquals($expected, $actual, "failed to return correct command bag");
        });

        $this->specify("it updates multiple properties", function () {
            $actual = $this->builder
                ->update([
                    'name' => 'chris',
                    'birthday' => 'april'
                ])
                ->getBag();

            $expected = $this->buildExpectedBag([
                'command' => Bag::COMMAND_UPDATE,
                'data' => [['name' => 'chris', 'birthday' => 'april']],
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
                ->type(Bag::ELEMENT_VERTEX)
                ->data($data) // alias withData()
                ->getBag();

            $expected = $this->buildExpectedBag([
                'command' => Bag::COMMAND_UPDATE,
                'data' => [['name' => 'chris', 'birthday' => 'april']],
                'target' => Bag::ELEMENT_VERTEX
            ]);

            $this->assertEquals($expected, $actual, "failed to return correct command bag");
        });
    }
}
