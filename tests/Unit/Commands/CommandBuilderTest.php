<?php
namespace Michaels\Spider\Test\Unit\Commands;

use Codeception\Specify;
use InvalidArgumentException;
use Michaels\Spider\Commands\Bag;
use Michaels\Spider\Commands\Builder;
use Michaels\Spider\Test\Stubs\CommandProcessorStub;

class CommandBuilderTest extends \PHPUnit_Framework_TestCase
{
    use Specify;

    /**
     * The Builder Itself
     * @var Builder
     */
    protected $builder;

    public function setup()
    {
        $this->builder = new Builder(new CommandProcessorStub());
    }

    public function buildExpected(array $properties)
    {
        $expected = (array)new Bag();
        foreach ($properties as $key => $value) {
            $expected[$key] = $value;
        }
        return json_encode($expected);
    }

    public function testClear()
    {
        $this->builder
            ->select()
            ->from('V');

        $this->builder->clear();

        $this->assertEquals(new Bag(), $this->builder->getCommandBag(), "failed to return an empty bag");
    }

    public function testRecordQueries()
    {
        $this->specify("it returns all data from a single record sendCommand", function () {
            $actual = $this->builder
                ->select()
                ->record("#12:6767")// byId() alias
                ->getCommand();

            $expected = $this->buildExpected([
                'command' => 'select',
                'projections' => [],
                'from' => "#12:6767"
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
                'command' => 'select',
                'projections' => ['price', 'certified'],
                'from' => "#12:6767"
            ]);

            $this->assertEquals($expected, $actual->getScript(), "failed to return correct command bag");

        });

        $this->specify("it returns specified data using a SELECT projections string", function () {
            $actual = $this->builder
                ->select('price, certified')
                ->record("#12:6767")// byId() alias
                ->getCommand();

            $expected = $this->buildExpected([
                'command' => 'select',
                'projections' => ['price', 'certified'],
                'from' => "#12:6767"
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
                'command' => 'select',
                'projections' => ['price', 'certified'],
                'from' => "#12:6767"
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
                'command' => 'select',
                'projections' => [],
                'from' => "V"
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
                'command' => 'select',
                'projections' => [],
                'from' => "V",
                'where' => [
                    ['certified', '=', "true", 'AND']
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
                'command' => 'select',
                'projections' => [],
                'from' => "V",
                'where' => [
                    ['certified', '=', "false", 'AND']
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
                'command' => 'select',
                'projections' => [],
                'from' => "V",
                'where' => [
                    ['certified', '=', "1", 'AND']
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
                'command' => 'select',
                'projections' => [],
                'from' => "V",
                'where' => [
                    ['certified', '=', "0", 'AND']
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
                'command' => 'select',
                'projections' => [],
                'from' => "V",
                'where' => [
                    ['certified', '=', "13", 'AND']
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
                'command' => 'select',
                'projections' => [],
                'from' => "V",
                'where' => [
                    ['certified', '=', "1.77", 'AND']
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
                'command' => 'select',
                'projections' => [],
                'from' => "V",
                'where' => [
                    ['certified', '=', "'yes'", 'AND']
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
                'command' => 'select',
                'projections' => [],
                'from' => "V",
                'where' => [
                    ['name', '=', "'michael'", 'AND'],
                    ['last', '=', "'wilson'", 'AND'],
                    ['certified', '=', "true", 'AND']
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
                'command' => 'select',
                'projections' => [],
                'from' => "V",
                'where' => [
                    ['name', '=', "'michael'", 'AND'],
                    ['last', '=', "'wilson'", 'AND'],
                    ['certified', '=', "true", 'AND']
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
                'command' => 'select',
                'projections' => [],
                'from' => "V",
                'where' => [
                    ['name', '=', "'michael'", 'AND'],
                    ['certified', '=', "true", 'AND']
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
                'command' => 'select',
                'projections' => [],
                'from' => "V",
                'where' => [
                    ['name', '=', "'michael'", 'AND'],
                    ['price', '>', "2", 'AND']
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
                'command' => 'select',
                'projections' => [],
                'from' => "V",
                'where' => [
                    ['name', '=', "'michael'", 'AND'],
                    ['last', '=', "'wilson'", 'OR'],
                    ['certified', '=', "true", 'OR']
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
                'command' => 'select',
                'projections' => [],
                'from' => "V",
                'where' => [
                    ['certified', '=', "true", 'AND'],
                    ['name', '=', "'michael'", 'OR'],

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

            $expected = "SELECT FROM V WHERE name = 'michael' OR price > 2";

            $expected = $this->buildExpected([
                'command' => 'select',
                'projections' => [],
                'from' => "V",
                'where' => [
                    ['name', '=', "'michael'", 'AND'],
                    ['price', '>', "2", 'OR']
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
                'command' => 'select',
                'projections' => [],
                'from' => "V",
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
                'command' => 'select',
                'projections' => [],
                'from' => "V",
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
                'command' => 'select',
                'projections' => [],
                'from' => "V",
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
                'command' => 'select',
                'projections' => [],
                'from' => "V",
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
                'command' => 'select',
                'projections' => [],
                'from' => "V",
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
                'command' => 'select',
                'projections' => [],
                'from' => "V",
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
                'command' => 'select',
                'projections' => [],
                'from' => "V",
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
                'command' => 'select',
                'projections' => [],
                'from' => "V",
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
                'command' => 'select',
                'projections' => [],
                'from' => "V",
                'orderBy' => ['price', 'owner'],
                'orderAsc' => true,
            ]);

            $this->assertEquals($expected, $actual->getScript(), "failed to return correct command bag");
        });
    }
}

