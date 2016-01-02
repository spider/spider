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
        $this->specify("it creates a complex batch", function() {
            $batch = new SqlBatch();
            $batch->begin();
            $batch->addStatement("a create statement", SqlBatch::CREATE_STATEMENT);
            $batch->addStatement("a select statement", SqlBatch::SELECT_STATEMENT);
            $batch->addStatement("another select statement", SqlBatch::SELECT_STATEMENT);
            $batch->addStatement("an update statement", SqlBatch::UPDATE_STATEMENT);
            $batch->addStatement("another create statement", SqlBatch::CREATE_STATEMENT);
            $batch->addStatement("a delete statement", SqlBatch::DELETE_STATEMENT);
            $batch->end();

            $actual = $batch->getScript();

            $expected = "begin\n";
            $expected .= "LET c1 = a create statement\n";
            $expected .= "LET s2 = a select statement\n";
            $expected .= "LET s3 = another select statement\n";
            $expected .= "LET u4 = an update statement\n";
            $expected .= "LET c5 = another create statement\n";
            $expected .= "LET d6 = a delete statement\n";
            $expected .= "commit retry 100\n";
            $expected .= 'return [$c1,$s2,$s3,$u4,$c5,$d6]';

            $this->assertEquals($expected, $actual, "failed to return correct script");
        });
    }
}
