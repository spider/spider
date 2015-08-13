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
                ->target("V")
                ->where('certified', 'yes')
                ->getCommandBag();

            $expected = $this->buildExpectedBag([
                'command' => Bag::COMMAND_RETRIEVE,
                'projections' => [],
                'target' => "V",
                'where' => [
                    ['certified', Bag::COMPARATOR_EQUAL, 'yes', Bag::CONJUNCTION_AND]
                ]
            ]);

            $this->assertEquals($expected, $actual, "failed to return correct command bag: true");
        });

        $this->specify("it adds several AND WHERE constraints", function () {
            $actual = $this->builder
                ->retrieve()
                ->target("V")
                ->where('name', 'michael')
                ->where('certified', true)
                ->getCommandBag();

            $expected = $this->buildExpectedBag([
                'command' => Bag::COMMAND_RETRIEVE,
                'projections' => [],
                'target' => "V",
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
                ->target("V")
                ->where(['name', '=', 'michael'])
                ->where('certified', true)
                ->getCommandBag();

            $expected = $this->buildExpectedBag([
                'command' => Bag::COMMAND_RETRIEVE,
                'projections' => [],
                'target' => "V",
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
                ->target("V")
                ->where([
                    ['name', '=', 'michael'],
                    ['price', '>', 2]
                ])
                ->getCommandBag();

            $expected = $this->buildExpectedBag([
                'command' => Bag::COMMAND_RETRIEVE,
                'projections' => [],
                'target' => "V",
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
                ->target("V")
                ->where('certified', true)
                ->where(['name', '=', 'michael', 'OR'])
                ->getCommandBag();

            $expected = $this->buildExpectedBag([
                'command' => Bag::COMMAND_RETRIEVE,
                'projections' => [],
                'target' => "V",
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
                ->target("V")
                ->where([
                    ['name', '=', 'michael'],
                    ['price', '>', 2, 'OR']
                ])
                ->getCommandBag();

            $expected = $this->buildExpectedBag([
                'command' => Bag::COMMAND_RETRIEVE,
                'projections' => [],
                'target' => "V",
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
                ->target("V")
                ->limit(2)
                ->getCommandBag();

            $expected = $this->buildExpectedBag([
                'command' => Bag::COMMAND_RETRIEVE,
                'projections' => [],
                'target' => "V",
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
                ->target("V")
                ->groupBy('certified')
                ->getCommandBag();

            $expected = $this->buildExpectedBag([
                'command' => Bag::COMMAND_RETRIEVE,
                'projections' => [],
                'target' => "V",
                'groupBy' => ['certified']
            ]);

            $this->assertEquals($expected, $actual, "failed to return correct command bag");
        });

        $this->specify("it groups results by a multiple fields array", function () {
            $actual = $this->builder
                ->retrieve()
                ->target("V")
                ->groupBy(['certified', 'price'])
                ->getCommandBag();

            $expected = $this->buildExpectedBag([
                'command' => Bag::COMMAND_RETRIEVE,
                'projections' => [],
                'target' => "V",
                'groupBy' => ['certified', 'price']
            ]);

            $this->assertEquals($expected, $actual, "failed to return correct command bag");
        });

        $this->specify("it groups results by a multiple fields string", function () {
            $actual = $this->builder
                ->retrieve()
                ->target("V")
                ->groupBy('certified, price')
                ->getCommandBag();

            $expected = $this->buildExpectedBag([
                'command' => Bag::COMMAND_RETRIEVE,
                'projections' => [],
                'target' => "V",
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
                ->target("V")
                ->orderBy('price')
                ->getCommandBag();

            $expected = $this->buildExpectedBag([
                'command' => Bag::COMMAND_RETRIEVE,
                'projections' => [],
                'target' => "V",
                'orderBy' => ['price']
            ]);

            $this->assertEquals($expected, $actual, "failed to return correct command bag");
        });

        $this->specify("it orders results by a field, desc", function () {
            $actual = $this->builder
                ->retrieve()
                ->target("V")
                ->orderBy('price')->desc()
                ->getCommandBag();

            $expected = $this->buildExpectedBag([
                'command' => Bag::COMMAND_RETRIEVE,
                'projections' => [],
                'target' => "V",
                'orderBy' => ['price'],
                'orderAsc' => false,
            ]);

            $this->assertEquals($expected, $actual, "failed to return correct command bag");
        });

        $this->specify("it orders results by a field, asc", function () {
            $actual = $this->builder
                ->retrieve()
                ->target("V")
                ->orderBy('price')->asc()
                ->getCommandBag();

            $expected = $this->buildExpectedBag([
                'command' => Bag::COMMAND_RETRIEVE,
                'projections' => [],
                'target' => "V",
                'orderBy' => ['price'],
                'orderAsc' => true,
            ]);

            $this->assertEquals($expected, $actual, "failed to return correct command bag");
        });

        $this->specify("it orders results by multiple fields, array", function () {
            $actual = $this->builder
                ->retrieve()
                ->target("V")
                ->orderBy(['price', 'owner'])
                ->getCommandBag();

            $expected = $this->buildExpectedBag([
                'command' => Bag::COMMAND_RETRIEVE,
                'projections' => [],
                'target' => "V",
                'orderBy' => ['price', 'owner'],
                'orderAsc' => true,
            ]);

            $this->assertEquals($expected, $actual, "failed to return correct command bag");
        });

        $this->specify("it orders results by multiple fields, string", function () {
            $actual = $this->builder
                ->retrieve()
                ->target("V")
                ->orderBy('price, owner')
                ->getCommandBag();

            $expected = $this->buildExpectedBag([
                'command' => Bag::COMMAND_RETRIEVE,
                'projections' => [],
                'target' => "V",
                'orderBy' => ['price', 'owner'],
                'orderAsc' => true,
            ]);

            $this->assertEquals($expected, $actual, "failed to return correct command bag");
        });
    }
}
