<?php
namespace Michaels\Spider\Test\Unit\Queries;

use Codeception\Specify;
use InvalidArgumentException;
use Michaels\Spider\Connections\Connection;
use Michaels\Spider\Drivers\OrientDB\OrientDriver;
use Michaels\Spider\Queries\QueryBuilder;

class QueryBuilderTest extends \PHPUnit_Framework_TestCase
{
    use Specify;

    protected $builder;

    public function setup()
    {
        $connection = new Connection(new OrientDriver(), [
            'hostname' => 'localhost',
            'port' => 2424,
            'username' => 'root',
            'password' => "root",
            'database' => 'VehicleHistoryGraph'
        ]);

        $this->builder = new QueryBuilder($connection);
    }

    public function testInstantiation()
    {
        $this->specify("it instantiates with a connection", function () {
            $actual = $this->builder->query("SELECT FROM Model LIMIT 2");

            $this->assertCount(2, $actual, 'failed to return 2 items from direct query');
        });
    }

    public function testRecordQueries()
    {
        $this->specify("it returns all data from a single record query", function () {
            $actual = $this->builder
                ->select()
                ->record("#12:6767")// byId() alias
                ->getScript();

            $expected = "SELECT FROM #12:6767";

            $this->assertEquals($expected, $actual, "failed to return a single record");
        });
    }

    public function testProjections()
    {
        $this->specify("it returns specified data using a SELECT projections array", function () {
            $actual = $this->builder
                ->select(['price', 'certified'])
                ->record("#12:6767")// byId() alias
                ->getScript();

            $expected = "SELECT price, certified FROM #12:6767";

            $this->assertEquals($expected, $actual, "failed to return projections");
        });

        $this->specify("it returns specified data using a SELECT projections string", function () {
            $actual = $this->builder
                ->select('price, certified')
                ->record("#12:6767")// byId() alias
                ->getScript();

            $expected = "SELECT price, certified FROM #12:6767";

            $this->assertEquals($expected, $actual, "failed to return projections");
        });

        $this->specify("it returns specified data using a only", function () {
            $actual = $this->builder
                ->select()
                ->record("#12:6767")// byId() alias
                ->only(['price', 'certified'])
                ->getScript();

            $expected = "SELECT price, certified FROM #12:6767";

            $this->assertEquals($expected, $actual, "failed to return projections");
        });

        $this->specify("it throws exception if projections is not array or string", function () {
            $actual = $this->builder
                ->select()
                ->record("#12:6767")// byId() alias
                ->only(3)
                ->getScript();

        }, ['throws' => new InvalidArgumentException("Projections must be a comma-separated string or an array")]);

        /*$this->specify("it throws exception if projections is an invalid string", function() {
            $actual = $this->builder
                ->select()
                ->record("#12:6767") // byId() alias
                ->only('only|this|one')
                ->getScript();

        }, ['throws' => new InvalidArgumentException("Projections must be a comma-separated string or an array")]);*/
    }

    public function testFrom()
    {
        $this->specify("it returns all records using `from()`", function () {
            $actual = $this->builder
                ->select()
                ->from("V")
                ->getScript();

            $expected = "SELECT FROM V";

            $this->assertEquals($expected, $actual, "failed to return correct script");
        });
    }

    public function testSingleWhereFiltersAndCastValues() //todo: split this into two tests
    {
        $this->specify("it filters by a single where equals constraint: boolean", function () {

            // True
            $actual = $this->builder
                ->select()
                ->from("V")
                ->where('certified', true)
                ->getScript();

            $expected = "SELECT FROM V WHERE certified = true";

            $this->assertEquals($expected, $actual, "failed to return correct script");

            // False
            $this->builder = $this->builder->reset();
            $actual = $this->builder
                ->select()
                ->from("V")
                ->where('certified', false)
                ->getScript();

            $expected = "SELECT FROM V WHERE certified = false";

            $this->assertEquals($expected, $actual, "failed to return correct script");
        });

        $this->specify("it filters by a single where equals constraint: int", function () {

            // 1 (not true)
            $actual = $this->builder
                ->select()
                ->from("V")
                ->where('certified', 1)
                ->getScript();

            $expected = "SELECT FROM V WHERE certified = 1";

            $this->assertEquals($expected, $actual, "failed to return correct script");

            // 0 (not false)
            $this->builder = $this->builder->reset();
            $actual = $this->builder
                ->select()
                ->from("V")
                ->where('certified', 0)
                ->getScript();

            $expected = "SELECT FROM V WHERE certified = 0";

            $this->assertEquals($expected, $actual, "failed to return correct script");

            // Whole number
            $this->builder = $this->builder->reset();
            $actual = $this->builder
                ->select()
                ->from("V")
                ->where('certified', 13)
                ->getScript();

            $expected = "SELECT FROM V WHERE certified = 13";

            $this->assertEquals($expected, $actual, "failed to return correct script");

            // Decimal (float)
            $this->builder = $this->builder->reset();
            $actual = $this->builder
                ->select()
                ->from("V")
                ->where('certified', 1.77)
                ->getScript();

            $expected = "SELECT FROM V WHERE certified = 1.77";

            $this->assertEquals($expected, $actual, "failed to return correct script");
        });

        $this->specify("it filters by a single where equals constraint: string", function () {
            $actual = $this->builder
                ->select()
                ->from("V")
                ->where('certified', 'yes')
                ->getScript();

            $expected = "SELECT FROM V WHERE certified = 'yes'";

            $this->assertEquals($expected, $actual, "failed to return correct script");
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
                ->getScript();

            $expected = "SELECT FROM V WHERE name = 'michael' AND last = 'wilson' AND certified = true";

            $this->assertEquals($expected, $actual, "failed to return correct script");
        });

        $this->specify("it adds several AND WHERE constraints", function () {
            $actual = $this->builder
                ->select()
                ->from("V")
                ->where('name', 'michael')
                ->andWhere('last', 'wilson')
                ->andWhere('certified', true)
                ->getScript();

            $expected = "SELECT FROM V WHERE name = 'michael' AND last = 'wilson' AND certified = true";

            $this->assertEquals($expected, $actual, "failed to return correct script");
        });

        $this->specify("it adds an array of WHERE AND constraints", function () {
            $actual = $this->builder
                ->select()
                ->from("V")
                ->where(['name', '=', 'michael'])
                ->where('certified', true)
                ->getScript();

            $expected = "SELECT FROM V WHERE name = 'michael' AND certified = true";

            $this->assertEquals($expected, $actual, "failed to return correct script");
        });

        $this->specify("it adds an array of an array of WHERE AND constraints", function () {
            $actual = $this->builder
                ->select()
                ->from("V")
                ->where([
                    ['name', '=', 'michael'],
                    ['price', '>', 2]
                ])
                ->getScript();

            $expected = "SELECT FROM V WHERE name = 'michael' AND price > 2";

            $this->assertEquals($expected, $actual, "failed to return correct script");
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
                ->getScript();

            $expected = "SELECT FROM V WHERE name = 'michael' OR last = 'wilson' OR certified = true";

            $this->assertEquals($expected, $actual, "failed to return correct script");
        });

        $this->specify("it adds an array of WHERE AND constraints", function () {
            $actual = $this->builder
                ->select()
                ->from("V")
                ->where('certified', true)
                ->where(['name', '=', 'michael', 'OR'])
                ->getScript();

            $expected = "SELECT FROM V WHERE certified = true OR name = 'michael'";

            $this->assertEquals($expected, $actual, "failed to return correct script");
        });

        $this->specify("it adds an array of an array of WHERE AND constraints", function () {
            $actual = $this->builder
                ->select()
                ->from("V")
                ->where([
                    ['name', '=', 'michael'],
                    ['price', '>', 2, 'OR']
                ])
                ->getScript();

            $expected = "SELECT FROM V WHERE name = 'michael' OR price > 2";

            $this->assertEquals($expected, $actual, "failed to return correct script");
        });
    }
}

