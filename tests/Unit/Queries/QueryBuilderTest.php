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

    public function testWhereFilters()
    {
        $this->specify("it filters by a single where equals constraint", function () {
            $actual = $this->builder
                ->select()
                ->from("V")
                ->where('certified', true)
                ->getScript();

            $expected = "SELECT FROM V WHERE certified = true";

            $this->assertEquals($expected, $actual, "failed to return correct script");
        });
    }
}

