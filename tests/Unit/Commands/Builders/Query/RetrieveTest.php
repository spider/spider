<?php
namespace Spider\Test\Unit\Commands\Builders\Query;

use Codeception\Specify;
use Spider\Commands\Bag;
use Spider\Graphs\ID as TargetID;
use InvalidArgumentException;

class RetrieveTest extends TestSetup
{
    use Specify;

    /* Retrieval Tests */
    public function testProjections()
    {
        $this->specify("it returns specified data using a SELECT projections array", function () {
            $actual = $this->builder
                ->select(['price', 'certified'])
                ->record("#12:6767")// byId() alias
                ->getCommandBag();

            $expected = $this->buildExpectedBag([
                'command' => Bag::COMMAND_RETRIEVE,
                'projections' => ['price', 'certified'],
                'target' => new TargetID("#12:6767")
            ]);

            $this->assertEquals($expected, $actual, "failed to return correct command bag");

        });

        $this->specify("it returns specified data using a SELECT projections string", function () {
            $actual = $this->builder
                ->select('price, certified')
                ->record("#12:6767")// byId() alias
                ->getCommandBag();

            $expected = $this->buildExpectedBag([
                'command' => Bag::COMMAND_RETRIEVE,
                'projections' => ['price', 'certified'],
                'target' => new TargetID("#12:6767")
            ]);

            $this->assertEquals($expected, $actual, "failed to return correct command bag");

        });

        $this->specify("it returns specified data using a only", function () {
            $actual = $this->builder
                ->select()
                ->record("#12:6767")// byId() alias
                ->only(['price', 'certified'])
                ->getCommandBag();

            $expected = $this->buildExpectedBag([
                'command' => Bag::COMMAND_RETRIEVE,
                'projections' => ['price', 'certified'],
                'target' => new TargetID("#12:6767")
            ]);

            $this->assertEquals($expected, $actual, "failed to return correct command bag");

        });

        $this->specify("it throws exception if projections is not array or string", function () {
            $this->builder
                ->select()
                ->record("#12:6767")// byId() alias
                ->only(3)
                ->getCommandBag();

        }, ['throws' => new InvalidArgumentException("Projections must be a comma-separated string or an array")]);

        /*$this->specify("it throws exception if projections is an invalid string", function() {
            $actual = $this->builder
                ->select()
                ->record("#12:6767") // byId() alias
                ->only('only|this|one')
                ->getCommandBag();

        }, ['throws' => new InvalidArgumentException("Projections must be a comma-separated string or an array")]);*/
    }

    public function testFrom()
    {
        $this->specify("it returns all records using `from()`", function () {
            $actual = $this->builder
                ->select()
                ->from("V")
                ->getCommandBag();

            $expected = $this->buildExpectedBag([
                'command' => Bag::COMMAND_RETRIEVE,
                'projections' => [],
                'target' => "V"
            ]);

            $this->assertEquals($expected, $actual, "failed to return correct command bag");
        });
    }

    public function testSingleWhereFiltersAndCastValues() //todo: split this into two tests
    {
        $this->specify("it filters by a single where equals constraint: boolean", function () {

            // True
            $actualTrue = $this->builder
                ->select()
                ->from("V")
                ->where('certified', true)
                ->getCommandBag();

            $expectedTrue = $this->buildExpectedBag([
                'command' => Bag::COMMAND_RETRIEVE,
                'projections' => [],
                'target' => "V",
                'where' => [
                    ['certified', Bag::COMPARATOR_EQUAL, true, Bag::CONJUNCTION_AND]
                ]
            ]);

            $this->assertEquals($expectedTrue, $actualTrue, "failed to return correct command bag: true");

            // False
            $this->builder->clear();
            $actualFalse = $this->builder
                ->select()
                ->from("V")
                ->where('certified', false)
                ->getCommandBag();

            $expectedFalse = $this->buildExpectedBag([
                'command' => Bag::COMMAND_RETRIEVE,
                'projections' => [],
                'target' => "V",
                'where' => [
                    ['certified', Bag::COMPARATOR_EQUAL, false, Bag::CONJUNCTION_AND]
                ]
            ]);

            $this->assertEquals($expectedFalse, $actualFalse, "failed to return correct command bag: false");
        });

        $this->specify("it filters by a single where equals constraint: int", function () {

            // 1 (not true)
            $actual = $this->builder
                ->select()
                ->from("V")
                ->where('certified', 1)
                ->getCommandBag();

            $expected = $this->buildExpectedBag([
                'command' => Bag::COMMAND_RETRIEVE,
                'projections' => [],
                'target' => "V",
                'where' => [
                    ['certified', Bag::COMPARATOR_EQUAL, 1, Bag::CONJUNCTION_AND]
                ]
            ]);

            $this->assertEquals($expected, $actual, "failed to return correct command bag");

            // 0 (not false)
            $this->builder->clear();
            $actual = $this->builder
                ->select()
                ->from("V")
                ->where('certified', 0)
                ->getCommandBag();

            $expected = $this->buildExpectedBag([
                'command' => Bag::COMMAND_RETRIEVE,
                'projections' => [],
                'target' => "V",
                'where' => [
                    ['certified', Bag::COMPARATOR_EQUAL, 0, Bag::CONJUNCTION_AND]
                ]
            ]);

            $this->assertEquals($expected, $actual, "failed to return correct command bag");

            // Whole number
            $this->builder->clear();
            $actual = $this->builder
                ->select()
                ->from("V")
                ->where('certified', 13)
                ->getCommandBag();

            $expected = $this->buildExpectedBag([
                'command' => Bag::COMMAND_RETRIEVE,
                'projections' => [],
                'target' => "V",
                'where' => [
                    ['certified', Bag::COMPARATOR_EQUAL, 13, Bag::CONJUNCTION_AND]
                ]
            ]);

            $this->assertEquals($expected, $actual, "failed to return correct command bag");

            // Decimal (float)
            $this->builder->clear();
            $actual = $this->builder
                ->select()
                ->from("V")
                ->where('certified', 1.77)
                ->getCommandBag();

            $expected = $this->buildExpectedBag([
                'command' => Bag::COMMAND_RETRIEVE,
                'projections' => [],
                'target' => "V",
                'where' => [
                    ['certified', Bag::COMPARATOR_EQUAL, 1.77, Bag::CONJUNCTION_AND]
                ]
            ]);

            $this->assertEquals($expected, $actual, "failed to return correct command bag");
        });

        $this->specify("it filters by a single where equals constraint: string", function () {
            $actual = $this->builder
                ->select()
                ->from("V")
                ->where('certified', 'yes')
                ->getCommandBag();

            $expected = $this->buildExpectedBag([
                'command' => Bag::COMMAND_RETRIEVE,
                'projections' => [],
                'target' => "V",
                'where' => [
                    ['certified', Bag::COMPARATOR_EQUAL, "yes", Bag::CONJUNCTION_AND]
                ]
            ]);

            $this->assertEquals($expected, $actual, "failed to return correct command bag");
        });
    }

    public function testMultipleAndWhereFilters()
    {
        $this->specify("it adds several AND WHERE constraints", function () {
            $actual = $this->builder
                ->select()
                ->from("V")
                ->where('name', 'michael')
                ->where('last', 'wilson')
                ->where('certified', true)
                ->getCommandBag();

            $expected = $this->buildExpectedBag([
                'command' => Bag::COMMAND_RETRIEVE,
                'projections' => [],
                'target' => "V",
                'where' => [
                    ['name', Bag::COMPARATOR_EQUAL, "michael", Bag::CONJUNCTION_AND],
                    ['last', Bag::COMPARATOR_EQUAL, "wilson", Bag::CONJUNCTION_AND],
                    ['certified', Bag::COMPARATOR_EQUAL, true, Bag::CONJUNCTION_AND]
                ]
            ]);

            $this->assertEquals($expected, $actual, "failed to return correct command bag");
        });

        $this->specify("it adds several AND WHERE constraints", function () {
            $actual = $this->builder
                ->select()
                ->from("V")
                ->where('name', 'michael')
                ->andWhere('last', 'wilson')
                ->andWhere('certified', true)
                ->getCommandBag();

            $expected = $this->buildExpectedBag([
                'command' => Bag::COMMAND_RETRIEVE,
                'projections' => [],
                'target' => "V",
                'where' => [
                    ['name', Bag::COMPARATOR_EQUAL, "michael", Bag::CONJUNCTION_AND],
                    ['last', Bag::COMPARATOR_EQUAL, "wilson", Bag::CONJUNCTION_AND],
                    ['certified', Bag::COMPARATOR_EQUAL, true, Bag::CONJUNCTION_AND]
                ]
            ]);

            $this->assertEquals($expected, $actual, "failed to return correct command bag");
        });

        $this->specify("it adds an array of WHERE AND constraints", function () {
            $actual = $this->builder
                ->select()
                ->from("V")
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
                ->select()
                ->from("V")
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
    }

    public function testMultipleOrWhereFilters()
    {
        $this->specify("it adds several OR WHERE constraints", function () {
            $actual = $this->builder
                ->select()
                ->from("V")
                ->where('name', 'michael')
                ->orWhere('last', 'wilson')
                ->orWhere('certified', true)
                ->getCommandBag();

            $expected = $this->buildExpectedBag([
                'command' => Bag::COMMAND_RETRIEVE,
                'projections' => [],
                'target' => "V",
                'where' => [
                    ['name', Bag::COMPARATOR_EQUAL, "michael", Bag::CONJUNCTION_AND],
                    ['last', Bag::COMPARATOR_EQUAL, "wilson", Bag::CONJUNCTION_OR],
                    ['certified', Bag::COMPARATOR_EQUAL, true, Bag::CONJUNCTION_OR]
                ]
            ]);

            $this->assertEquals($expected, $actual, "failed to return correct command bag");
        });

        $this->specify("it adds an array of WHERE OR constraints", function () {
            $actual = $this->builder
                ->select()
                ->from("V")
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
                ->select()
                ->from("V")
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
                ->select()
                ->from("V")
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
                ->select()
                ->from("V")
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
                ->select()
                ->from("V")
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
                ->select()
                ->from("V")
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
                ->select()
                ->from("V")
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
                ->select()
                ->from("V")
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
                ->select()
                ->from("V")
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
                ->select()
                ->from("V")
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
                ->select()
                ->from("V")
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
