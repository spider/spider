<?php
//namespace Spider\Test\Unit\Commands\Languages;
//
//use Codeception\Specify;
//use Spider\Commands\Bag;
//use Spider\Commands\Command;
//use Spider\Commands\Languages\OrientSQL\CommandProcessor;
//use Spider\Graphs\ID;
//
//class OrientSqlProcessorTest extends \PHPUnit_Framework_TestCase
//{
//    use Specify;
//
//    protected function getWheres()
//    {
//        return [
//            ['one', Bag::COMPARATOR_EQUAL, 'one', Bag::CONJUNCTION_AND],
//            ['two', Bag::COMPARATOR_GT, 2, Bag::CONJUNCTION_AND],
//            ['three', Bag::COMPARATOR_LT, 3.14, Bag::CONJUNCTION_OR],
//            ['four', Bag::COMPARATOR_EQUAL, true, Bag::CONJUNCTION_AND]
//        ];
//    }
//
//    protected function getWhereSql()
//    {
//        return " WHERE one = 'one' AND two > 2 OR three < 3.14 AND four = true";
//    }
//
//    protected function getData()
//    {
//        return ['one' => 1, 'two' => 'two', 'three' => false];
//    }
//
//    public function testNewInserts()
//    {
//        $this->specify("it processes a simple insert bag", function () {
//
//            $bag = new Bag();
//            $bag->command = Bag::COMMAND_CREATE;
//            $bag->target = 'target'; // don't forget about TargetID
//            $bag->data = $this->getData();
//
//            $expected = $this->getExpectedCommand('insert-simple');
//
//            $actual = (new CommandProcessor())->process($bag);
//            $this->assertEquals($expected, $actual, 'failed to return expected Command for simple select bag');
//        });
//    }
//
//    public function getExpectedCommand($alias)
//    {
//
//    }
//
//    public function insertSimple()
//    {
//        $query = 'INSERT INTO target';
//        $query .= ' CONTENT ' . json_encode($this->getData());
//        $query .= ' RETURN @this';
//
//        $command = new Command($query);
//        $command->setScriptLanguage('OrientSQL');
//        $expected = $command;
//
//        return $expected;
//    }
//
//    /* Begin Tests */
////    public function testInsert()
////    {
////        $this->specify("it processes a simple insert bag", function () {
////
////            $bag = new Bag();
////            $bag->command = Bag::COMMAND_CREATE;
////            $bag->target = 'target'; // don't forget about TargetID
////            $bag->data = $this->getData();
////
////            $query = 'INSERT INTO target';
////            $query .= ' CONTENT ' . json_encode($bag->data);
////            $query .= ' RETURN @this';
////
////            $command = new Command($query);
////            $command->setScriptLanguage('OrientSQL');
////            $expected = $command;
////
////            $actual = (new CommandProcessor())->process($bag);
////            $this->assertEquals($expected, $actual, 'failed to return expected Command for simple select bag');
////        });
////    }
////
////    public function testUpdate()
////    {
////        $this->specify("it processes a simple update bag", function () {
////
////            $bag = new Bag();
////            $bag->command = Bag::COMMAND_UPDATE;
////            $bag->target = '#12:1'; // don't forget about TargetID
////            $bag->data = $this->getData();
////
////            $query = 'UPDATE #12:1';
////            $query .= ' MERGE ' . json_encode($bag->data);
////            $query .= ' RETURN AFTER';
////
////            $command = new Command($query);
////            $command->setScriptLanguage('OrientSQL');
////            $expected = $command;
////
////            $actual = (new CommandProcessor())->process($bag);
////            $this->assertEquals($expected, $actual, 'failed to return expected Command for simple select bag');
////        });
////
////        $this->specify("it processes a complex update bag", function () {
////
////            $bag = new Bag();
////            $bag->command = Bag::COMMAND_UPDATE;
////            $bag->target = 'target'; // don't forget about TargetID
////            $bag->data = $this->getData();
////            $bag->where = $this->getWheres();
////            $bag->limit = 10;
////
////            $query = 'UPDATE target';
////            $query .= ' MERGE ' . json_encode($bag->data);
////            $query .= $this->getWhereSql();
////            $query .= ' LIMIT 10';
////            $query .= ' RETURN AFTER';
////
////            $command = new Command($query);
////            $command->setScriptLanguage('OrientSQL');
////            $expected = $command;
////
////            $actual = (new CommandProcessor())->process($bag);
////            $this->assertEquals($expected, $actual, 'failed to return expected Command for simple select bag');
////        });
////    }
////
////    public function testDelete()
////    {
////        $this->specify("it processes a simple delete bag", function () {
////
////            $bag = new Bag();
////            $bag->command = Bag::COMMAND_DELETE;
////            $bag->target = new ID("#12:1");
////            $bag->data = $this->getData();
////
////            $query = 'DELETE VERTEX #12:1';
////
////            $command = new Command($query);
////            $command->setScriptLanguage('OrientSQL');
////            $expected = $command;
////
////            $actual = (new CommandProcessor())->process($bag);
////            $this->assertEquals($expected, $actual, 'failed to return expected Command for simple select bag');
////        });
////
////        $this->specify("it processes a complex delete bag", function () {
////
////            $bag = new Bag();
////            $bag->command = Bag::COMMAND_DELETE;
////            $bag->target = 'target'; // don't forget about TargetID
////            $bag->where = $this->getWheres();
////            $bag->limit = 10;
////
////            $query = 'DELETE VERTEX FROM target';
////            $query .= $this->getWhereSql();
////            $query .= ' LIMIT 10';
////
////            $command = new Command($query);
////            $command->setScriptLanguage('OrientSQL');
////            $expected = $command;
////
////            $actual = (new CommandProcessor())->process($bag);
////            $this->assertEquals($expected, $actual, 'failed to return expected Command for simple select bag');
////        });
////    }
////
////    public function testSelect()
////    {
////        $this->specify("it processes a simple select bag", function () {
////
////            $bag = new Bag();
////            $bag->command = Bag::COMMAND_RETRIEVE;
////            $bag->target = 'target';
////
////            $query = 'SELECT';
////            $query .= ' FROM target';
////
////            $command = new Command($query);
////            $command->setScriptLanguage('OrientSQL');
////            $command->setRw('read');
////            $expected = $command;
////
////            $actual = (new CommandProcessor())->process($bag);
////            $this->assertEquals($expected, $actual, 'failed to return expected Command for simple select bag');
////        });
////
////        $this->specify("it processes where constraints in select", function () {
////
////            $bag = new Bag();
////            $bag->command = Bag::COMMAND_RETRIEVE;
////            $bag->target = 'target'; // don't forget about TargetID
////            $bag->where = $this->getWheres();
////
////            $query = 'SELECT';
////            $query .= ' FROM target';
////            $query .= $this->getWhereSql();
////
////            $command = new Command($query);
////            $command->setScriptLanguage('OrientSQL');
////            $command->setRw('read');
////            $expected = $command;
////
////            $actual = (new CommandProcessor())->process($bag);
////            $this->assertEquals($expected, $actual, 'failed to return expected Command for simple select bag');
////        });
////
////        $this->specify("it processes a complex select bag", function () {
////
////            $bag = new Bag();
////            $bag->command = Bag::COMMAND_RETRIEVE;
////            $bag->projections = ['field1', 'field2'];
////            $bag->target = 'target'; // don't forget about TargetID
////            $bag->limit = 3;
////            $bag->groupBy = ['groupField'];
////            $bag->orderBy = ['orderField'];
////            $bag->orderAsc = false;
////            $bag->where = $this->getWheres();
////
////            $query = 'SELECT field1, field2';
////            $query .= ' FROM target';
////            $query .= $this->getWhereSql();
////            $query .= ' GROUP BY groupField';
////            $query .= ' ORDER BY orderField DESC';
////            $query .= ' LIMIT 3';
////
////            $command = new Command($query);
////            $command->setScriptLanguage('OrientSQL');
////            $command->setRw('read');
////            $expected = $command;
////
////            $actual = (new CommandProcessor())->process($bag);
////            $this->assertEquals($expected, $actual, 'failed to return expected Command');
////        });
////    }
//}
