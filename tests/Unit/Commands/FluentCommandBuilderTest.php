<?php
namespace Spider\Test\Unit\Commands;

use Codeception\Specify;
use InvalidArgumentException;
use Spider\Commands\Bag;
use Spider\Commands\Builder;
use Spider\Test\Stubs\CommandProcessorStub;
use Spider\Test\Stubs\ConnectionStub;

/**
 * This tests the fluent part of the command builder.
 * It only checks to see if the Commands\Bag was correctly built
 * from method chaining (->select()->from->etc...)
 *
 * This does not test the end script, only the Command Bag that was built
 *
 * The retrieval mechanisms are tested in `ConnectedCommandBuilderTest`
 * @package Spider\Test\Unit\Commands
 */
class FluentCommandBuilderTest extends \PHPUnit_Framework_TestCase
{
    use Specify;

    /**
     * The Builder Itself
     * @var Builder
     */
    protected $builder;

    public function setup()
    {
//        $this->builder = new Builder(new CommandProcessorStub());
        $this->builder = new Builder(new CommandProcessorStub(), new ConnectionStub());

    }

    public function buildExpected(array $properties)
    {
        $expected = (array)new Bag();
        foreach ($properties as $key => $value) {
            $expected[$key] = $value;
        }
        return json_encode($expected);
    }

    /* Begin Tests */
    public function testClear()
    {
        $this->builder
            ->select()
            ->from('V');

        $this->builder->clear();

        $this->assertEquals(new Bag(), $this->builder->getCommandBag(), "failed to return an empty bag");
    }

    /* Retrieval Tests */
    public function testRecordQueries()
    {
        $this->specify("it returns all data from a single record sendCommand", function () {
            $actual = $this->builder
                ->select()
                ->record("#12:6767")// byId() alias
                ->getCommand();

            $expected = $this->buildExpected([
                'command' => Bag::COMMAND_RETRIEVE,
                'projections' => [],
                'target' => "#12:6767"
            ]);

            $this->assertEquals($expected, $actual->getScript(), "failed to return correct command bag");
        });
    }

    public function testProjections()
    {
        $this->specify("it returns specified data using a SELECT projections array", function () {
            $actual = $this->builder
                ->select(['price', 'certified'])
                ->record("#12:6767")// byId() alias
                ->getCommand();

            $expected = $this->buildExpected([
                'command' => Bag::COMMAND_RETRIEVE,
                'projections' => ['price', 'certified'],
                'target' => "#12:6767"
            ]);

            $this->assertEquals($expected, $actual->getScript(), "failed to return correct command bag");

        });

        $this->specify("it returns specified data using a SELECT projections string", function () {
            $actual = $this->builder
                ->select('price, certified')
                ->record("#12:6767")// byId() alias
                ->getCommand();

            $expected = $this->buildExpected([
                'command' => Bag::COMMAND_RETRIEVE,
                'projections' => ['price', 'certified'],
                'target' => "#12:6767"
            ]);

            $this->assertEquals($expected, $actual->getScript(), "failed to return correct command bag");

        });

        $this->specify("it returns specified data using a only", function () {
            $actual = $this->builder
                ->select()
                ->record("#12:6767")// byId() alias
                ->only(['price', 'certified'])
                ->getCommand();

            $expected = $this->buildExpected([
                'command' => Bag::COMMAND_RETRIEVE,
                'projections' => ['price', 'certified'],
                'target' => "#12:6767"
            ]);

            $this->assertEquals($expected, $actual->getScript(), "failed to return correct command bag");

        });

        $this->specify("it throws exception if projections is not array or string", function () {
            $this->builder
                ->select()
                ->record("#12:6767")// byId() alias
                ->only(3)
                ->getCommand();

        }, ['throws' => new InvalidArgumentException("Projections must be a comma-separated string or an array")]);

        /*$this->specify("it throws exception if projections is an invalid string", function() {
            $actual = $this->builder
                ->select()
                ->record("#12:6767") // byId() alias
                ->only('only|this|one')
                ->getCommand();

        }, ['throws' => new InvalidArgumentException("Projections must be a comma-separated string or an array")]);*/
    }

    public function testFrom()
    {
        $this->specify("it returns all records using `from()`", function () {
            $actual = $this->builder
                ->select()
                ->from("V")
                ->getCommand();

            $expected = $this->buildExpected([
                'command' => Bag::COMMAND_RETRIEVE,
                'projections' => [],
                'target' => "V"
            ]);

            $this->assertEquals($expected, $actual->getScript(), "failed to return correct command bag");
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
                ->getCommand();

            $expectedTrue = $this->buildExpected([
                'command' => Bag::COMMAND_RETRIEVE,
                'projections' => [],
                'target' => "V",
                'where' => [
                    ['certified', Bag::COMPARATOR_EQUAL, true, Bag::CONJUNCTION_AND]
                ]
            ]);

            $this->assertEquals($expectedTrue, $actualTrue->getScript(), "failed to return correct command bag: true");

            // False
            $this->builder->clear();
            $actualFalse = $this->builder
                ->select()
                ->from("V")
                ->where('certified', false)
                ->getCommand();

            $expectedFalse = $this->buildExpected([
                'command' => Bag::COMMAND_RETRIEVE,
                'projections' => [],
                'target' => "V",
                'where' => [
                    ['certified', Bag::COMPARATOR_EQUAL, false, Bag::CONJUNCTION_AND]
                ]
            ]);

            $this->assertEquals($expectedFalse, $actualFalse->getScript(), "failed to return correct command bag: false");
        });

        $this->specify("it filters by a single where equals constraint: int", function () {

            // 1 (not true)
            $actual = $this->builder
                ->select()
                ->from("V")
                ->where('certified', 1)
                ->getCommand();

            $expected = $this->buildExpected([
                'command' => Bag::COMMAND_RETRIEVE,
                'projections' => [],
                'target' => "V",
                'where' => [
                    ['certified', Bag::COMPARATOR_EQUAL, 1, Bag::CONJUNCTION_AND]
                ]
            ]);

            $this->assertEquals($expected, $actual->getScript(), "failed to return correct command bag");

            // 0 (not false)
            $this->builder->clear();
            $actual = $this->builder
                ->select()
                ->from("V")
                ->where('certified', 0)
                ->getCommand();

            $expected = $this->buildExpected([
                'command' => Bag::COMMAND_RETRIEVE,
                'projections' => [],
                'target' => "V",
                'where' => [
                    ['certified', Bag::COMPARATOR_EQUAL, 0, Bag::CONJUNCTION_AND]
                ]
            ]);

            $this->assertEquals($expected, $actual->getScript(), "failed to return correct command bag");

            // Whole number
            $this->builder->clear();
            $actual = $this->builder
                ->select()
                ->from("V")
                ->where('certified', 13)
                ->getCommand();

            $expected = $this->buildExpected([
                'command' => Bag::COMMAND_RETRIEVE,
                'projections' => [],
                'target' => "V",
                'where' => [
                    ['certified', Bag::COMPARATOR_EQUAL, 13, Bag::CONJUNCTION_AND]
                ]
            ]);

            $this->assertEquals($expected, $actual->getScript(), "failed to return correct command bag");

            // Decimal (float)
            $this->builder->clear();
            $actual = $this->builder
                ->select()
                ->from("V")
                ->where('certified', 1.77)
                ->getCommand();

            $expected = $this->buildExpected([
                'command' => Bag::COMMAND_RETRIEVE,
                'projections' => [],
                'target' => "V",
                'where' => [
                    ['certified', Bag::COMPARATOR_EQUAL, 1.77, Bag::CONJUNCTION_AND]
                ]
            ]);

            $this->assertEquals($expected, $actual->getScript(), "failed to return correct command bag");
        });

        $this->specify("it filters by a single where equals constraint: string", function () {
            $actual = $this->builder
                ->select()
                ->from("V")
                ->where('certified', 'yes')
                ->getCommand();

            $expected = $this->buildExpected([
                'command' => Bag::COMMAND_RETRIEVE,
                'projections' => [],
                'target' => "V",
                'where' => [
                    ['certified', Bag::COMPARATOR_EQUAL, "yes", Bag::CONJUNCTION_AND]
                ]
            ]);

            $this->assertEquals($expected, $actual->getScript(), "failed to return correct command bag");
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
                ->getCommand();

            $expected = $this->buildExpected([
                'command' => Bag::COMMAND_RETRIEVE,
                'projections' => [],
                'target' => "V",
                'where' => [
                    ['name', Bag::COMPARATOR_EQUAL, "michael", Bag::CONJUNCTION_AND],
                    ['last', Bag::COMPARATOR_EQUAL, "wilson", Bag::CONJUNCTION_AND],
                    ['certified', Bag::COMPARATOR_EQUAL, true, Bag::CONJUNCTION_AND]
                ]
            ]);

            $this->assertEquals($expected, $actual->getScript(), "failed to return correct command bag");
        });

        $this->specify("it adds several AND WHERE constraints", function () {
            $actual = $this->builder
                ->select()
                ->from("V")
                ->where('name', 'michael')
                ->andWhere('last', 'wilson')
                ->andWhere('certified', true)
                ->getCommand();

            $expected = $this->buildExpected([
                'command' => Bag::COMMAND_RETRIEVE,
                'projections' => [],
                'target' => "V",
                'where' => [
                    ['name', Bag::COMPARATOR_EQUAL, "michael", Bag::CONJUNCTION_AND],
                    ['last', Bag::COMPARATOR_EQUAL, "wilson", Bag::CONJUNCTION_AND],
                    ['certified', Bag::COMPARATOR_EQUAL, true, Bag::CONJUNCTION_AND]
                ]
            ]);

            $this->assertEquals($expected, $actual->getScript(), "failed to return correct command bag");
        });

        $this->specify("it adds an array of WHERE AND constraints", function () {
            $actual = $this->builder
                ->select()
                ->from("V")
                ->where(['name', '=', 'michael'])
                ->where('certified', true)
                ->getCommand();

            $expected = $this->buildExpected([
                'command' => Bag::COMMAND_RETRIEVE,
                'projections' => [],
                'target' => "V",
                'where' => [
                    ['name', Bag::COMPARATOR_EQUAL, "michael", Bag::CONJUNCTION_AND],
                    ['certified', Bag::COMPARATOR_EQUAL, true, Bag::CONJUNCTION_AND]
                ]
            ]);

            $this->assertEquals($expected, $actual->getScript(), "failed to return correct command bag");
        });

        $this->specify("it adds an array of an array of WHERE AND constraints", function () {
            $actual = $this->builder
                ->select()
                ->from("V")
                ->where([
                    ['name', '=', 'michael'],
                    ['price', '>', 2]
                ])
                ->getCommand();

            $expected = $this->buildExpected([
                'command' => Bag::COMMAND_RETRIEVE,
                'projections' => [],
                'target' => "V",
                'where' => [
                    ['name', Bag::COMPARATOR_EQUAL, "michael", Bag::CONJUNCTION_AND],
                    ['price', Bag::COMPARATOR_GT, 2, Bag::CONJUNCTION_AND]
                ]
            ]);

            $this->assertEquals($expected, $actual->getScript(), "failed to return correct command bag");
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
                ->getCommand();

            $expected = $this->buildExpected([
                'command' => Bag::COMMAND_RETRIEVE,
                'projections' => [],
                'target' => "V",
                'where' => [
                    ['name', Bag::COMPARATOR_EQUAL, "michael", Bag::CONJUNCTION_AND],
                    ['last', Bag::COMPARATOR_EQUAL, "wilson", 'OR'],
                    ['certified', Bag::COMPARATOR_EQUAL, true, 'OR']
                ]
            ]);

            $this->assertEquals($expected, $actual->getScript(), "failed to return correct command bag");
        });

        $this->specify("it adds an array of WHERE OR constraints", function () {
            $actual = $this->builder
                ->select()
                ->from("V")
                ->where('certified', true)
                ->where(['name', '=', 'michael', 'OR'])
                ->getCommand();

            $expected = $this->buildExpected([
                'command' => Bag::COMMAND_RETRIEVE,
                'projections' => [],
                'target' => "V",
                'where' => [
                    ['certified', Bag::COMPARATOR_EQUAL, true, Bag::CONJUNCTION_AND],
                    ['name', Bag::COMPARATOR_EQUAL, "michael", 'OR'],

                ]
            ]);

            $this->assertEquals($expected, $actual->getScript(), "failed to return correct command bag");
        });

        $this->specify("it adds an array of an array of WHERE AND constraints", function () {
            $actual = $this->builder
                ->select()
                ->from("V")
                ->where([
                    ['name', '=', 'michael'],
                    ['price', '>', 2, 'OR']
                ])
                ->getCommand();

            $expected = $this->buildExpected([
                'command' => Bag::COMMAND_RETRIEVE,
                'projections' => [],
                'target' => "V",
                'where' => [
                    ['name', Bag::COMPARATOR_EQUAL, "michael", Bag::CONJUNCTION_AND],
                    ['price', Bag::COMPARATOR_GT, 2, 'OR']
                ]
            ]);

            $this->assertEquals($expected, $actual->getScript(), "failed to return correct command bag");
        });
    }

    public function testLimit()
    {
        $this->specify("it adds a specified limit", function () {
            $actual = $this->builder
                ->select()
                ->from("V")
                ->limit(2)
                ->getCommand();

            $expected = $this->buildExpected([
                'command' => Bag::COMMAND_RETRIEVE,
                'projections' => [],
                'target' => "V",
                'limit' => 2
            ]);

            $this->assertEquals($expected, $actual->getScript(), "failed to return correct command bag");
        });
    }

    public function testGroupBy()
    {
        $this->specify("it groups results by a single field", function () {
            $actual = $this->builder
                ->select()
                ->from("V")
                ->groupBy('certified')
                ->getCommand();

            $expected = $this->buildExpected([
                'command' => Bag::COMMAND_RETRIEVE,
                'projections' => [],
                'target' => "V",
                'groupBy' => ['certified']
            ]);

            $this->assertEquals($expected, $actual->getScript(), "failed to return correct command bag");
        });

        $this->specify("it groups results by a multiple fields array", function () {
            $actual = $this->builder
                ->select()
                ->from("V")
                ->groupBy(['certified', 'price'])
                ->getCommand();

            $expected = $this->buildExpected([
                'command' => Bag::COMMAND_RETRIEVE,
                'projections' => [],
                'target' => "V",
                'groupBy' => ['certified', 'price']
            ]);

            $this->assertEquals($expected, $actual->getScript(), "failed to return correct command bag");
        });

        $this->specify("it groups results by a multiple fields string", function () {
            $actual = $this->builder
                ->select()
                ->from("V")
                ->groupBy('certified, price')
                ->getCommand();

            $expected = $this->buildExpected([
                'command' => Bag::COMMAND_RETRIEVE,
                'projections' => [],
                'target' => "V",
                'groupBy' => ['certified', 'price']
            ]);

            $this->assertEquals($expected, $actual->getScript(), "failed to return correct command bag");
        });
    }

    function testOrderyBy()
    {
        $this->specify("it orders results by a field, asc by default", function () {
            $actual = $this->builder
                ->select()
                ->from("V")
                ->orderBy('price')
                ->getCommand();

            $expected = $this->buildExpected([
                'command' => Bag::COMMAND_RETRIEVE,
                'projections' => [],
                'target' => "V",
                'orderBy' => ['price']
            ]);

            $this->assertEquals($expected, $actual->getScript(), "failed to return correct command bag");
        });

        $this->specify("it orders results by a field, desc", function () {
            $actual = $this->builder
                ->select()
                ->from("V")
                ->orderBy('price')->desc()
                ->getCommand();

            $expected = $this->buildExpected([
                'command' => Bag::COMMAND_RETRIEVE,
                'projections' => [],
                'target' => "V",
                'orderBy' => ['price'],
                'orderAsc' => false,
            ]);

            $this->assertEquals($expected, $actual->getScript(), "failed to return correct command bag");
        });

        $this->specify("it orders results by a field, asc", function () {
            $actual = $this->builder
                ->select()
                ->from("V")
                ->orderBy('price')->asc()
                ->getCommand();

            $expected = $this->buildExpected([
                'command' => Bag::COMMAND_RETRIEVE,
                'projections' => [],
                'target' => "V",
                'orderBy' => ['price'],
                'orderAsc' => true,
            ]);

            $this->assertEquals($expected, $actual->getScript(), "failed to return correct command bag");
        });

        $this->specify("it orders results by multiple fields, array", function () {
            $actual = $this->builder
                ->select()
                ->from("V")
                ->orderBy(['price', 'owner'])
                ->getCommand();

            $expected = $this->buildExpected([
                'command' => Bag::COMMAND_RETRIEVE,
                'projections' => [],
                'target' => "V",
                'orderBy' => ['price', 'owner'],
                'orderAsc' => true,
            ]);

            $this->assertEquals($expected, $actual->getScript(), "failed to return correct command bag");
        });

        $this->specify("it orders results by multiple fields, string", function () {
            $actual = $this->builder
                ->select()
                ->from("V")
                ->orderBy('price, owner')
                ->getCommand();

            $expected = $this->buildExpected([
                'command' => Bag::COMMAND_RETRIEVE,
                'projections' => [],
                'target' => "V",
                'orderBy' => ['price', 'owner'],
                'orderAsc' => true,
            ]);

            $this->assertEquals($expected, $actual->getScript(), "failed to return correct command bag");
        });
    }

    /* Create Tests */
    public function testCreateRecords()
    {
        $this->specify("it inserts a single record and returns nothing", function () {
            $record = [
                'first' => 'first-value',
                'second' => 'second-value'
            ];

            $actual = $this->builder
                ->into('target')
                ->insert($record);

            $expected = $this->buildExpected([
                'command' => Bag::COMMAND_CREATE,
                'target' => "target",
                'data' => $record
            ]);

            $this->assertEquals($expected, $actual->getScript(), "failed to return correct command bag");
        });

        $this->specify("it inserts a single record and returns nothing", function () {
            $record = [
                ['first' => 'first-value', 'A', 'a'],
                ['first' => 'second-value', 'B', 'b']
            ];

            $actual = $this->builder
                ->into('target')
                ->insert($record);

            $expected = $this->buildExpected([
                'command' => Bag::COMMAND_CREATE,
                'target' => "target",
                'data' => $record
            ]);

            $this->assertEquals($expected, $actual->getScript(), "failed to return correct command bag");
        });
    }

    /* Return tests for CUD */
    public function testReturnValuesOnCUD()
    {
        $this->specify("it returns nothing by default", function () {
            $actual = $this->builder
                ->getCommand();

            $expected = $this->buildExpected([
                'return' => false
            ]);

            $this->assertEquals($expected, $actual->getScript(), "failed to return correct command bag");
        });

        $this->specify("it returns a whole object", function () {
            $actual = $this->builder
                ->return()
                ->getCommand();

            $expected = $this->buildExpected([
                'return' => true
            ]);

            $this->assertEquals($expected, $actual->getScript(), "failed to return correct command bag");
        });

        $this->specify("it returns a single value", function () {
            $actual = $this->builder
                ->return('username')
                ->getCommand();

            $expected = $this->buildExpected([
                'return' => ['username']
            ]);

            $this->assertEquals($expected, $actual->getScript(), "failed to return correct command bag");
        });

        $this->specify("it several properties from array", function () {
            $actual = $this->builder
                ->return(['username', 'password'])
                ->getCommand();

            $expected = $this->buildExpected([
                'return' => ['username', 'password']
            ]);

            $this->assertEquals($expected, $actual->getScript(), "failed to return correct command bag");
        });

        $this->specify("it several properties from csv string (one space)", function () {
            $actual = $this->builder
                ->return('username, password')
                ->getCommand();

            $expected = $this->buildExpected([
                'return' => ['username', 'password']
            ]);

            $this->assertEquals($expected, $actual->getScript(), "failed to return correct command bag");
        });

        $this->specify("it several properties from csv string (no spaces)", function () {
            $actual = $this->builder
                ->return('username,password')
                ->getCommand();

            $expected = $this->buildExpected([
                'return' => ['username', 'password']
            ]);

            $this->assertEquals($expected, $actual->getScript(), "failed to return correct command bag");
        });

        $this->specify("it several properties from csv string (many spaces)", function () {
            $actual = $this->builder
                ->return('username,           password')
                ->getCommand();

            $expected = $this->buildExpected([
                'return' => ['username', 'password']
            ]);

            $this->assertEquals($expected, $actual->getScript(), "failed to return correct command bag");
        });
    }
}
