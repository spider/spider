<?php
namespace Spider\Test\Unit\Commands\Builders\Builder;

use Codeception\Specify;
use Spider\Commands\Bag;
use Spider\Test\Stubs\CommandProcessorStub;
use Spider\Test\Unit\Commands\Builders\Builder\TestSetup;

/* BaseBuilder tested elsewhere */
class BaseTest extends TestSetup
{
    use Specify;

    /* Record(s) target Tests */
    public function testRecordTargets()
    {
        $this->specify("it adds a single record id via `record()`", function () {
            $actual = $this->builder
                ->record(3)
                ->getBag();

            $expected = $this->buildExpectedBag([
                'where' => [[Bag::ELEMENT_ID, Bag::COMPARATOR_EQUAL, 3, Bag::CONJUNCTION_AND]]
            ]);

            $this->assertEquals($expected, $actual, "failed to return correct command bag");
        });

        $this->specify("it adds multiple records via ids", function () {
            $actual = $this->builder
                ->records([1, 2, 3])
                ->getBag();

            $expected = $this->buildExpectedBag([
                'where' => [[Bag::ELEMENT_ID, Bag::COMPARATOR_IN, [1, 2, 3], Bag::CONJUNCTION_AND]]
            ]);

            $this->assertEquals($expected, $actual, "failed to return correct command bag");
        });
    }
}
