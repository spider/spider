<?php
namespace Michaels\Spider\Test\Unit\Drivers\OrientDB;

use Codeception\Specify;
use Michaels\Spider\Commands\Bag;
use Michaels\Spider\Commands\Command;
use Michaels\Spider\Drivers\OrientDB\CommandProcessor;

/* ToDo: split this into more bite sized tests */
class CommandProcessorTest extends \PHPUnit_Framework_TestCase
{
    use Specify;

    public function testComplexCommandProcess()
    {
        $this->specify("it processes a complex CommandBag", function () {

            $bag = new Bag();
            $bag->command = 'select';
            $bag->projections = ['field1', 'field2'];
            $bag->from = 'target';
            $bag->limit = 3;
            $bag->groupBy = ['groupField'];
            $bag->orderBy = ['orderField'];
            $bag->orderAsc = false;
            $bag->where = [
                ['one', '=', '1', 'AND'],
                ['two', '>', '2', 'AND'],
                ['three', '<', '3', 'OR'],
                ['four', '>=', '4', 'AND']
            ];

            $query = 'SELECT field1, field2';
            $query .= ' FROM target';
            $query .= ' WHERE one = 1 AND two > 2 OR three < 3 AND four >= 4';
            $query .= ' GROUP BY groupField';
            $query .= ' ORDER BY orderField DESC';
            $query .= ' LIMIT 3';

            $expected = new Command($query);

            $actual = (new CommandProcessor())->process($bag);
            $this->assertEquals($expected, $actual, 'failed to return expected Command');
        });
    }
}

