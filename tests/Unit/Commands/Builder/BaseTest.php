<?php
namespace Spider\Test\Unit\Commands\Builder;

use Codeception\Specify;
use Spider\Commands\Bag;
use Spider\Graphs\ID as TargetID;

class BaseTest extends TestSetup
{
    use Specify;

    /* Command Bag Tests */
    public function testClear()
    {
        $this->builder
            ->select()
            ->from('V');

        $this->builder->clear();

        $this->assertEquals(new Bag(), $this->builder->getCommandBag(), "failed to return an empty bag");
    }

    /* ToDo: getCommandBag() */

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

        $this->specify("it drops multiple records via ids", function () {
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

    /* Return tests */
    public function testReturnValuesOnCUD()
    {
        $this->specify("it returns nothing by default", function () {
            $actual = $this->builder
                ->getCommandBag();

            $expected = $this->buildExpectedBag([
                'return' => false
            ]);

            $this->assertEquals($expected, $actual, "failed to return correct command bag");
        });

        $this->specify("it returns a whole object", function () {
            $actual = $this->builder
                ->return()
                ->getCommandBag();

            $expected = $this->buildExpectedBag([
                'return' => true
            ]);

            $this->assertEquals($expected, $actual, "failed to return correct command bag");
        });

        $this->specify("it returns a single value", function () {
            $actual = $this->builder
                ->return('username')
                ->getCommandBag();

            $expected = $this->buildExpectedBag([
                'return' => ['username']
            ]);

            $this->assertEquals($expected, $actual, "failed to return correct command bag");
        });

        $this->specify("it several properties from array", function () {
            $actual = $this->builder
                ->return(['username', 'password'])
                ->getCommandBag();

            $expected = $this->buildExpectedBag([
                'return' => ['username', 'password']
            ]);

            $this->assertEquals($expected, $actual, "failed to return correct command bag");
        });

        $this->specify("it several properties from csv string (one space)", function () {
            $actual = $this->builder
                ->return('username, password')
                ->getCommandBag();

            $expected = $this->buildExpectedBag([
                'return' => ['username', 'password']
            ]);

            $this->assertEquals($expected, $actual, "failed to return correct command bag");
        });

        $this->specify("it several properties from csv string (no spaces)", function () {
            $actual = $this->builder
                ->return('username,password')
                ->getCommandBag();

            $expected = $this->buildExpectedBag([
                'return' => ['username', 'password']
            ]);

            $this->assertEquals($expected, $actual, "failed to return correct command bag");
        });

        $this->specify("it several properties from csv string (many spaces)", function () {
            $actual = $this->builder
                ->return('username,           password')
                ->getCommandBag();

            $expected = $this->buildExpectedBag([
                'return' => ['username', 'password']
            ]);

            $this->assertEquals($expected, $actual, "failed to return correct command bag");
        });
    }

    /* Begin Tests */
    public function testRetrievalMethods()
    {
        $this->specify("it gets all records", function () {
            $actual = $this->builder
                ->select()
                ->from('v')
                ->all();

            $expected = $this->buildExpectedCommand([
                'command' => Bag::COMMAND_RETRIEVE,
                'projections' => [],
                'target' => "v",
                'limit' => false
            ]);

            $this->assertEquals($expected, $actual->getScript(), 'failed to return correct command');
        });
    }

    /* ToDo: first(), dispatch(), one() */
}
