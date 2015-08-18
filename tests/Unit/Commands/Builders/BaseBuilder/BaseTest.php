<?php
namespace Spider\Test\Unit\Commands\Builders\BaseBuilder;

use Codeception\Specify;
use Spider\Commands\Bag;
use Spider\Test\Unit\Commands\Builders\TestSetup;

class BaseTest extends TestSetup
{
    use Specify;

    /* Manage the Command Bag */
    public function testCreateBag()
    {
        $this->assertEquals(new Bag(), $this->builder->getBag(), "failed to return an empty bag");
    }

    public function testClearBag()
    {
        $this->builder
            ->retrieve()
            ->type(Bag::ELEMENT_VERTEX);

        $this->builder->clear();

        $this->assertEquals(new Bag(), $this->builder->getBag(), "failed to return an empty bag");
    }

    /* Projections tests */
    /* Also thoroughly tests csvToArray() */
    public function testProjections()
    {
        $this->specify("it returns nothing by default", function () {
            $actual = $this->builder
                ->getBag();

            $expected = $this->buildExpectedBag([
                'projections' => []
            ]);

            $this->assertEquals($expected, $actual, "failed to return correct command bag");
        });

        $this->specify("it returns a single value", function () {
            $actual = $this->builder
                ->projections('username')
                ->getBag();

            $expected = $this->buildExpectedBag([
                'projections' => ['username']
            ]);

            $this->assertEquals($expected, $actual, "failed to return correct command bag");
        });

        $this->specify("it several properties from array", function () {
            $actual = $this->builder
                ->projections(['username', 'password'])
                ->getBag();

            $expected = $this->buildExpectedBag([
                'projections' => ['username', 'password']
            ]);

            $this->assertEquals($expected, $actual, "failed to return correct command bag");
        });

        $this->specify("it several properties from csv string (one space)", function () {
            $actual = $this->builder
                ->projections('username, password')
                ->getBag();

            $expected = $this->buildExpectedBag([
                'projections' => ['username', 'password']
            ]);

            $this->assertEquals($expected, $actual, "failed to return correct command bag");
        });

        $this->specify("it returns several properties from csv string (no spaces)", function () {
            $actual = $this->builder
                ->projections('username,password')
                ->getBag();

            $expected = $this->buildExpectedBag([
                'projections' => ['username', 'password']
            ]);

            $this->assertEquals($expected, $actual, "failed to return correct command bag");
        });

        $this->specify("it returns several properties from csv string (many spaces)", function () {
            $actual = $this->builder
                ->projections('username,           password')
                ->getBag();

            $expected = $this->buildExpectedBag([
                'projections' => ['username', 'password']
            ]);

            $this->assertEquals($expected, $actual, "failed to return correct command bag");
        });

        $this->specify("it throws exception if projections is not array or string", function () {
            $this->builder
                ->retrieve()
                ->type(Bag::ELEMENT_VERTEX)
                ->projections(3)
                ->getBag();

        }, ['throws' => new \InvalidArgumentException("Projections must be a comma-separated string or an array")]);

    }
}
