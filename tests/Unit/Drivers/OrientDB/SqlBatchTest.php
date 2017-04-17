<?php
namespace Spider\Test\Unit\Drivers\OrientDB;

use Codeception\Specify;
use Spider\Commands\Builder;
use Spider\Commands\Command;
use Spider\Commands\Languages\OrientSQL\SqlBatch;
use Spider\Drivers\OrientDB\Driver as OrientDriver;
use Spider\Test\Fixtures\Graph;
use Spider\Test\Fixtures\OrientFixture;
use Spider\Test\Unit\Drivers\BaseTestSuite;

class SqlBatchTest extends \PHPUnit_Framework_TestCase
{
    use Specify;

    public function testCreatesBatch()
    {
        $this->specify("it creates a simple, 2 statement batch", function() {
            $batch = new SqlBatch();
            $batch->begin();
            $batch->addStatement("create vertex Account set name = 'Luke'");
            $batch->addStatement("select from City where name = 'London'");
            $batch->end();

            $actual = $batch->getScript();

            $expected = "begin\n";
            $expected .= "LET t1 = create vertex Account set name = 'Luke'\n";
            $expected .= "LET t2 = select from City where name = 'London'\n";
            $expected .= "commit retry 100\n";
            $expected .= 'return [$t1,$t2]';

            $this->assertEquals($expected, $actual, "failed to return correct script");
        });
    }
}
