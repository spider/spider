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
                ->internalUpdate(['name'=> 'chris'])
                ->getBag();

            $expected = $this->buildExpectedBag([
                'command' => Bag::COMMAND_UPDATE,
                'data' => [['name' => 'chris']]
            ]);

            $this->assertEquals($expected, $actual, "failed to return correct command bag");
        });

        $this->specify("it updates a single record with a target and constraint", function () {
            $actual = $this->builder
                ->internalRetrieve()
                ->internalWhere(['username', Bag::COMPARATOR_EQUAL, 'chrismichaels84', Bag::CONJUNCTION_AND])
                ->internalWhere([Bag::ELEMENT_LABEL, Bag::COMPARATOR_EQUAL, 'target', Bag::CONJUNCTION_AND])
                ->limit(1)
                ->internalUpdate(['name'=> 'chris'])
                ->getBag();

            $expected = $this->buildExpectedBags([
                [
                    'command' => Bag::COMMAND_RETRIEVE,
                    'target' => Bag::ELEMENT_VERTEX,
                    'limit' => 1,
                    'where' => [['username', Bag::COMPARATOR_EQUAL, 'chrismichaels84', Bag::CONJUNCTION_AND],[Bag::ELEMENT_LABEL, Bag::COMPARATOR_EQUAL, 'target', Bag::CONJUNCTION_AND]],
                ],
                [
                    'command' => Bag::COMMAND_UPDATE,
                    'data' => [['name' => 'chris']]
                ]
            ]);

            $this->assertEquals($expected, $actual, "failed to return correct command bag");
        });

        $this->specify("it updates multiple properties", function () {
            $actual = $this->builder
                ->internalUpdate([
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
                ->internalUpdate()
                ->data($data)
                ->getBag();

            $expected = $this->buildExpectedBag([
                'command' => Bag::COMMAND_UPDATE,
                'data' => [['name' => 'chris', 'birthday' => 'april']],
            ]);

            $this->assertEquals($expected, $actual, "failed to return correct command bag");
        });
    }
}
