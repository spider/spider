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
        $this->specify("it deletes a single vertex", function () {
            $actual = $this->builder
                ->internalWhere(['name', Bag::COMPARATOR_EQUAL, 'test-name', Bag::CONJUNCTION_AND])
                ->internalDelete()
                ->getBag();

            $expected = $this->buildExpectedBag([
                'where' => [
                    ['name', Bag::COMPARATOR_EQUAL, 'test-name', Bag::CONJUNCTION_AND]
                ],
                'delete' => true,
            ]);

            $this->assertEquals($expected, $actual, "failed to return correct command bag");
        });
    }
}
