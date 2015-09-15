<?php
namespace Spider\Test\Unit\Commands\Builders\BaseBuilder;

use Codeception\Specify;
use Spider\Commands\Bag;
use Spider\Test\Unit\Commands\Builders\TestSetup;

class DeleteTest extends TestSetup
{
    use Specify;

    public function testDelete()
    {
        $this->specify("it drops a single record dispatching from `delete()`", function () {
            $actual = $this->builder
                ->internalRetrieve()
                ->internalWhere(['name', Bag::COMPARATOR_EQUAL, 'test-name', Bag::CONJUNCTION_AND])
                ->internalDelete()
                ->getBag();

            $expected = $this->buildExpectedBags([
                [
                    'command' => Bag::COMMAND_RETRIEVE,
                    'where' => [
                        ['name', Bag::COMPARATOR_EQUAL, 'test-name', Bag::CONJUNCTION_AND]
                    ]
                ],
                ['command' => Bag::COMMAND_DELETE],
            ]);

            $this->assertEquals($expected, $actual, "failed to return correct command bag");
        });
    }
}
