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

    public function testWhereFilters()
    {
        $this->specify("it filters by a single where equals constraint", function () {
            $actual = $this->builder
                ->retrieve()
                ->type(Bag::ELEMENT_VERTEX)
                ->where('certified', 'yes')
                ->getBag();

            $expected = $this->buildExpectedBag([
                'command' => Bag::COMMAND_RETRIEVE,
                'projections' => [],
                'target' => Bag::ELEMENT_VERTEX,
                'where' => [
                    ['certified', Bag::COMPARATOR_EQUAL, 'yes', Bag::CONJUNCTION_AND]
                ]
            ]);

            $this->assertEquals($expected, $actual, "failed to return correct command bag: true");
        });

        $this->specify("it adds several AND WHERE constraints", function () {
            $actual = $this->builder
                ->retrieve()
                ->type(Bag::ELEMENT_VERTEX)
                ->where('name', 'michael')
                ->where('certified', true)
                ->getBag();

            $expected = $this->buildExpectedBag([
                'command' => Bag::COMMAND_RETRIEVE,
                'projections' => [],
                'target' => Bag::ELEMENT_VERTEX,
                'where' => [
                    ['name', Bag::COMPARATOR_EQUAL, "michael", Bag::CONJUNCTION_AND],
                    ['certified', Bag::COMPARATOR_EQUAL, true, Bag::CONJUNCTION_AND]
                ]
            ]);

            $this->assertEquals($expected, $actual, "failed to return correct command bag");
        });

        $this->specify("it adds an array of WHERE AND constraints", function () {
            $actual = $this->builder
                ->retrieve()
                ->type(Bag::ELEMENT_VERTEX)
                ->where(['name', '=', 'michael'])
                ->where('certified', true)
                ->getBag();

            $expected = $this->buildExpectedBag([
                'command' => Bag::COMMAND_RETRIEVE,
                'projections' => [],
                'target' => Bag::ELEMENT_VERTEX,
                'where' => [
                    ['name', Bag::COMPARATOR_EQUAL, "michael", Bag::CONJUNCTION_AND],
                    ['certified', Bag::COMPARATOR_EQUAL, true, Bag::CONJUNCTION_AND]
                ]
            ]);

            $this->assertEquals($expected, $actual, "failed to return correct command bag");
        });

        $this->specify("it adds an array of an array of WHERE AND constraints", function () {
            $actual = $this->builder
                ->retrieve()
                ->type(Bag::ELEMENT_VERTEX)
                ->where([
                    ['name', '=', 'michael'],
                    ['price', '>', 2]
                ])
                ->getBag();

            $expected = $this->buildExpectedBag([
                'command' => Bag::COMMAND_RETRIEVE,
                'projections' => [],
                'target' => Bag::ELEMENT_VERTEX,
                'where' => [
                    ['name', Bag::COMPARATOR_EQUAL, "michael", Bag::CONJUNCTION_AND],
                    ['price', Bag::COMPARATOR_GT, 2, Bag::CONJUNCTION_AND]
                ]
            ]);

            $this->assertEquals($expected, $actual, "failed to return correct command bag");
        });

        $this->specify("it adds an array of WHERE OR constraints", function () {
            $actual = $this->builder
                ->retrieve()
                ->type(Bag::ELEMENT_VERTEX)
                ->where('certified', true)
                ->where(['name', '=', 'michael', 'OR'])
                ->getBag();

            $expected = $this->buildExpectedBag([
                'command' => Bag::COMMAND_RETRIEVE,
                'projections' => [],
                'target' => Bag::ELEMENT_VERTEX,
                'where' => [
                    ['certified', Bag::COMPARATOR_EQUAL, true, Bag::CONJUNCTION_AND],
                    ['name', Bag::COMPARATOR_EQUAL, "michael", Bag::CONJUNCTION_OR],

                ]
            ]);

            $this->assertEquals($expected, $actual, "failed to return correct command bag");
        });

        $this->specify("it adds an array of an array of WHERE AND constraints", function () {
            $actual = $this->builder
                ->retrieve()
                ->type(Bag::ELEMENT_VERTEX)
                ->where([
                    ['name', '=', 'michael'],
                    ['price', '>', 2, 'OR']
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
    }

    public function testLimit()
    {
        $this->specify("it adds a specified limit", function () {
            $actual = $this->builder
                ->retrieve()
                ->type(Bag::ELEMENT_VERTEX)
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
                ->retrieve()
                ->type(Bag::ELEMENT_VERTEX)
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
                ->retrieve()
                ->type(Bag::ELEMENT_VERTEX)
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
                ->retrieve()
                ->type(Bag::ELEMENT_VERTEX)
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
                ->retrieve()
                ->type(Bag::ELEMENT_VERTEX)
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
                ->retrieve()
                ->type(Bag::ELEMENT_VERTEX)
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
                ->retrieve()
                ->type(Bag::ELEMENT_VERTEX)
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

        //~ $this->specify("it orders results by multiple fields, array", function () {
            //~ $actual = $this->builder
                //~ ->retrieve()
                //~ ->type(Bag::ELEMENT_VERTEX)
                //~ ->orderBy(['price', 'owner'])
                //~ ->getBag();
//~
            //~ $expected = $this->buildExpectedBag([
                //~ 'command' => Bag::COMMAND_RETRIEVE,
                //~ 'projections' => [],
                //~ 'target' => Bag::ELEMENT_VERTEX,
                //~ 'orderBy' => [['price', Bag::ORDER_ASC],['owner', Bag::ORDER_ASC]],
            //~ ]);
//~
            //~ $this->assertEquals($expected, $actual, "failed to return correct command bag");
        //~ });
//~
        //~ $this->specify("it orders results by multiple fields, string", function () {
            //~ $actual = $this->builder
                //~ ->retrieve()
                //~ ->type(Bag::ELEMENT_VERTEX)
                //~ ->orderBy('price, owner')
                //~ ->getBag();
//~
            //~ $expected = $this->buildExpectedBag([
                //~ 'command' => Bag::COMMAND_RETRIEVE,
                //~ 'projections' => [],
                //~ 'target' => Bag::ELEMENT_VERTEX,
                //~ 'orderBy' => ['price', 'owner'],
            //~ ]);
//~
            //~ $this->assertEquals($expected, $actual, "failed to return correct command bag");
        //~ });
    }
}
