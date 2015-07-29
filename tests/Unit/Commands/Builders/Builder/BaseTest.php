<?php
namespace Spider\Test\Unit\Commands\Builders\Builder;

use Codeception\Specify;
use Spider\Commands\Bag;
use Spider\Graphs\ID as TargetID;
use Spider\Test\Unit\Commands\Builders\Builder\TestSetup;

class BaseTest extends TestSetup
{
    use Specify;

    /* Record(s) target Tests */
    public function testRecordTargets()
    {
        $this->specify("it adds a single record id via `record()`", function () {
            $actual = $this->builder
                ->record(3)
                ->getCommandBag();

            $expected = $this->buildExpectedBag([
                'target' => new TargetID(3)
            ]);

            $this->assertEquals($expected, $actual, "failed to return correct command bag");
        });

        $this->specify("it adds multiple records via ids", function () {
            $actual = $this->builder
                ->records([1, 2, 3])
                ->getCommandBag();

            $expected = $this->buildExpectedBag([
                'target' => [
                    new TargetID(1),
                    new TargetID(2),
                    new TargetID(3),
                ],
            ]);

            $this->assertEquals($expected, $actual, "failed to return correct command bag");
        });
    }

    public function testRetrievalMethods()
    {
        $this->specify("it gets all records", function () {
            $actual = $this->builder
                ->select()
                ->from('v')
                ->all()
                ->getCommandBag();

            $expected = $this->buildExpectedBag([
                'command' => Bag::COMMAND_RETRIEVE,
                'projections' => [],
                'target' => "v",
                'limit' => false
            ]);

            $this->assertEquals($expected, $actual, 'failed to return correct command');
        });

        $this->specify("it gets one records", function () {
            $actual = $this->builder
                ->select()
                ->from('v')
                ->one()
                ->getCommandBag();

            $expected = $this->buildExpectedBag([
                'command' => Bag::COMMAND_RETRIEVE,
                'projections' => [],
                'target' => "v",
                'limit' => 1
            ]);

            $this->assertEquals($expected, $actual, 'failed to return correct command');
        });

        $this->specify("it gets first records", function () {
            $actual = $this->builder
                ->select()
                ->from('v')
                ->first()
                ->getCommandBag();

            $expected = $this->buildExpectedBag([
                'command' => Bag::COMMAND_RETRIEVE,
                'projections' => [],
                'target' => "v",
                'limit' => 1
            ]);

            $this->assertEquals($expected, $actual, 'failed to return correct command');
        });
    }
}
