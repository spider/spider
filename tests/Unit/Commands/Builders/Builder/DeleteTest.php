<?php
namespace Spider\Test\Unit\Commands\Builders\Builder;

use Codeception\Specify;
use Spider\Commands\Bag;

class DeleteTest extends TestSetup
{
    use Specify;

    public function testDrop()
    {
        $this->specify("it drops a single record dispatching from `delete()`", function () {
            $actual = $this->builder
                ->delete(3)
                ->getBag();

            $expected = $this->buildExpectedBag([
                'where' => [[Bag::ELEMENT_ID, Bag::COMPARATOR_EQUAL, 3, Bag::CONJUNCTION_AND]],
                'delete' => true,
            ]);

            $this->assertEquals($expected, $actual, "failed to return correct command bag");
        });

        $this->specify("it drops multiple records dispatching from `delete()`", function () {
            $actual = $this->builder
                ->delete([1, 2, 3])
                ->getBag();

            $expected = $this->buildExpectedBag([
                'where' => [[Bag::ELEMENT_ID, Bag::COMPARATOR_IN, [1, 2, 3], Bag::CONJUNCTION_AND]],
                'delete' => true,
            ]);

            $this->assertEquals($expected, $actual, "failed to return correct command bag");
        });

        $this->specify("it drops multiple records via constraints", function () {
            $actual = $this->builder
                ->delete()
                ->from('target')
                ->where('birthday', 'apr')
                ->getBag();

            $expected = $this->buildExpectedBag([
                'where' => [[Bag::ELEMENT_LABEL, Bag::COMPARATOR_EQUAL, 'target', Bag::CONJUNCTION_AND],['birthday', Bag::COMPARATOR_EQUAL, 'apr', Bag::CONJUNCTION_AND]],
                'delete' => true,
            ]);

            $this->assertEquals($expected, $actual, "failed to return correct command bag");
        });
    }
}
