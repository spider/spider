<?php
namespace Spider\Test\Unit\Commands\Builders\BaseBuilder;

use Codeception\Specify;
use Spider\Commands\Bag;
use InvalidArgumentException;
use Spider\Test\Unit\Commands\Builders\TestSetup;

class RetrieveTest extends TestSetup
{
    use Specify;

    /* Retrieval Tests */
    // Projections tested in BaseTest

    public function testConstraints()
    {
        $this->specify("it adds a single array of a valid constraint", function () {
            $actual = $this->builder
                ->internalRetrieve()
                ->internalWhere(['name', Bag::COMPARATOR_EQUAL, 'michael', Bag::CONJUNCTION_OR])
                ->getBag();

            $expected = $this->buildExpectedBag([
                'command' => Bag::COMMAND_RETRIEVE,
                'projections' => [],
                'target' => Bag::ELEMENT_VERTEX,
                'where' => [
                    ['name', Bag::COMPARATOR_EQUAL, "michael", Bag::CONJUNCTION_OR],
                ]
            ]);

            $this->assertEquals($expected, $actual, "failed to return correct command bag");
        });

        $this->specify("it adds multiple valid constraint", function () {
            $actual = $this->builder
                ->internalRetrieve()
                ->internalWhere([
                    ['name', Bag::COMPARATOR_EQUAL, 'michael', Bag::CONJUNCTION_AND],
                    ['price', Bag::COMPARATOR_GT, 2, Bag::CONJUNCTION_OR]
                ])
                ->getBag();

            $expected = $this->buildExpectedBag([
                'command' => Bag::COMMAND_RETRIEVE,
                'projections' => [],
                'target' => Bag::ELEMENT_VERTEX,
                'where' => [
                    ['name', Bag::COMPARATOR_EQUAL, "michael", Bag::CONJUNCTION_AND],
                    ['price', Bag::COMPARATOR_GT, 2, Bag::CONJUNCTION_OR]
                ]
            ]);

            $this->assertEquals($expected, $actual, "failed to return correct command bag");
        });

        $this->specify("it throws an exception for invalid constraint: too few parameters", function () {
            $this->builder
                ->internalRetrieve()
                ->internalWhere(
                    ['name', Bag::COMPARATOR_EQUAL, 'michael']
                )
                ->getBag();
        }, ['throws' => 'Exception']);

        $this->specify("it throws an exception for invalid constraint: operator not a constant", function () {
            $this->builder
                ->internalRetrieve()
                ->internalWhere(
                    ['name', '=', 'michael', Bag::CONJUNCTION_AND]
                )
                ->getBag();
        }, ['throws' => 'Exception']);

        $this->specify("it throws an exception for invalid constraint: conjunction not a constant", function () {
            $this->builder
                ->internalRetrieve()
                ->internalWhere(
                    [true, Bag::COMPARATOR_EQUAL, 'michael', 'AND']
                )
                ->getBag();
        }, ['throws' => 'Exception']);
    }

    public function testLimit()
    {
        $this->specify("it adds a specified limit", function () {
            $actual = $this->builder
                ->internalRetrieve()
                ->limit(2)
                ->getBag();

            $expected = $this->buildExpectedBag([
                'command' => Bag::COMMAND_RETRIEVE,
                'projections' => [],
                'target' => Bag::ELEMENT_VERTEX,
                'limit' => 2
            ]);

            $this->assertEquals($expected, $actual, "failed to return correct command bag");
        });
    }

    public function testGroupBy()
    {
        $this->specify("it groups results by a single field", function () {
            $actual = $this->builder
                ->internalRetrieve()
                ->groupBy('certified')
                ->getBag();

            $expected = $this->buildExpectedBag([
                'command' => Bag::COMMAND_RETRIEVE,
                'projections' => [],
                'target' => Bag::ELEMENT_VERTEX,
                'groupBy' => ['certified']
            ]);

            $this->assertEquals($expected, $actual, "failed to return correct command bag");
        });

        $this->specify("it groups results by a multiple fields array", function () {
            $actual = $this->builder
                ->internalRetrieve()
                ->groupBy(['certified', 'price'])
                ->getBag();

            $expected = $this->buildExpectedBag([
                'command' => Bag::COMMAND_RETRIEVE,
                'projections' => [],
                'target' => Bag::ELEMENT_VERTEX,
                'groupBy' => ['certified', 'price']
            ]);

            $this->assertEquals($expected, $actual, "failed to return correct command bag");
        });

        $this->specify("it groups results by a multiple fields string", function () {
            $actual = $this->builder
                ->internalRetrieve()
                ->groupBy('certified, price')
                ->getBag();

            $expected = $this->buildExpectedBag([
                'command' => Bag::COMMAND_RETRIEVE,
                'projections' => [],
                'target' => Bag::ELEMENT_VERTEX,
                'groupBy' => ['certified', 'price']
            ]);

            $this->assertEquals($expected, $actual, "failed to return correct command bag");
        });
    }

    public function testOrderyBy()
    {
        $this->specify("it orders results by a field, asc by default", function () {
            $actual = $this->builder
                ->internalRetrieve()
                ->orderBy('price')
                ->getBag();

            $expected = $this->buildExpectedBag([
                'command' => Bag::COMMAND_RETRIEVE,
                'projections' => [],
                'target' => Bag::ELEMENT_VERTEX,
                'orderBy' => [['price', Bag::ORDER_ASC]]
            ]);

            $this->assertEquals($expected, $actual, "failed to return correct command bag");
        });

        $this->specify("it orders results by a field, desc", function () {
            $actual = $this->builder
                ->internalRetrieve()
                ->orderBy('price', Bag::ORDER_DESC)
                ->getBag();

            $expected = $this->buildExpectedBag([
                'command' => Bag::COMMAND_RETRIEVE,
                'projections' => [],
                'target' => Bag::ELEMENT_VERTEX,
                'orderBy' => [['price', Bag::ORDER_DESC]],
            ]);

            $this->assertEquals($expected, $actual, "failed to return correct command bag");
        });

        $this->specify("it orders results by a field, asc", function () {
            $actual = $this->builder
                ->internalRetrieve()
                ->orderBy('price', Bag::ORDER_ASC)
                ->getBag();

            $expected = $this->buildExpectedBag([
                'command' => Bag::COMMAND_RETRIEVE,
                'projections' => [],
                'target' => Bag::ELEMENT_VERTEX,
                'orderBy' => [['price', Bag::ORDER_ASC]],
            ]);

            $this->assertEquals($expected, $actual, "failed to return correct command bag");
        });
    }
}
