<?php
namespace Spider\Test\Unit\Drivers\OrientDB;

use Codeception\Specify;
use Spider\Commands\Bag;
use Spider\Commands\Command;
use Spider\Drivers\OrientDB\CommandProcessor;

/* ToDo: split this into more bite sized tests */
class CommandProcessorTest extends \PHPUnit_Framework_TestCase
{
    use Specify;

    public function testComplexCommandProcess()
    {
        $this->specify("it processes a complex CommandBag", function () {

            $bag = new Bag();
            $bag->command = Bag::COMMAND_RETRIEVE;
            $bag->projections = ['field1', 'field2'];
            $bag->target = 'target';
            $bag->limit = 3;
            $bag->groupBy = ['groupField'];
            $bag->orderBy = ['orderField'];
            $bag->orderAsc = false;
            $bag->where = [
                ['one', Bag::COMPARATOR_EQUAL, 'one', Bag::CONJUNCTION_AND],
                ['two', Bag::COMPARATOR_GT, 2, Bag::CONJUNCTION_AND],
                ['three', Bag::COMPARATOR_LT, 3, Bag::CONJUNCTION_OR],
                ['four', Bag::COMPARATOR_EQUAL, true, Bag::CONJUNCTION_AND]
            ];

            $query = 'SELECT field1, field2';
            $query .= ' FROM target';
            $query .= " WHERE one = 'one' AND two > 2 OR three < 3 AND four = true";
            $query .= ' GROUP BY groupField';
            $query .= ' ORDER BY orderField DESC';
            $query .= ' LIMIT 3';

            $expected = new Command($query);

            $actual = (new CommandProcessor())->process($bag);
            $this->assertEquals($expected, $actual, 'failed to return expected Command');
        });
    }
}

