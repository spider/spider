<?php
namespace Spider\Test\Unit\Commands\Builders\BaseBuilder;

use Codeception\Specify;
use Spider\Commands\Bag;
use Spider\Test\Unit\Commands\Builders\TestSetup;

class BaseTest extends TestSetup
{
    use Specify;

    /* Command Bag Tests */
    public function testCreate()
    {
        $this->assertEquals(new Bag(), $this->builder->getCommandBag(), "failed to return an empty bag");
    }

    public function testClear()
    {
        $this->builder
            ->retrieve()
            ->target('V');

        $this->builder->clear();

        $this->assertEquals(new Bag(), $this->builder->getCommandBag(), "failed to return an empty bag");
    }

    /* Projections tests */
    /* Also thoroughly tests csvToArray() */
    public function testProjections()
    {
        $this->specify("it returns nothing by default", function () {
            $actual = $this->builder
                ->getCommandBag();

            $expected = $this->buildExpectedBag([
                'projections' => []
            ]);

            $this->assertEquals($expected, $actual, "failed to return correct command bag");
        });

        $this->specify("it returns a single value", function () {
            $actual = $this->builder
                ->projections('username')
                ->getCommandBag();

            $expected = $this->buildExpectedBag([
                'projections' => ['username']
            ]);

            $this->assertEquals($expected, $actual, "failed to return correct command bag");
        });

        $this->specify("it several properties from array", function () {
            $actual = $this->builder
                ->projections(['username', 'password'])
                ->getCommandBag();

            $expected = $this->buildExpectedBag([
                'projections' => ['username', 'password']
            ]);

            $this->assertEquals($expected, $actual, "failed to return correct command bag");
        });

        $this->specify("it several properties from csv string (one space)", function () {
            $actual = $this->builder
                ->projections('username, password')
                ->getCommandBag();

            $expected = $this->buildExpectedBag([
                'projections' => ['username', 'password']
            ]);

            $this->assertEquals($expected, $actual, "failed to return correct command bag");
        });

        $this->specify("it returns several properties from csv string (no spaces)", function () {
            $actual = $this->builder
                ->projections('username,password')
                ->getCommandBag();

            $expected = $this->buildExpectedBag([
                'projections' => ['username', 'password']
            ]);

            $this->assertEquals($expected, $actual, "failed to return correct command bag");
        });

        $this->specify("it returns several properties from csv string (many spaces)", function () {
            $actual = $this->builder
                ->projections('username,           password')
                ->getCommandBag();

            $expected = $this->buildExpectedBag([
                'projections' => ['username', 'password']
            ]);

            $this->assertEquals($expected, $actual, "failed to return correct command bag");
        });
    }
}
