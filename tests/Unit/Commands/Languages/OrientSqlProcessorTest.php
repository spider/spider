<?php
namespace Spider\Test\Unit\Commands\Languages;

use Codeception\Specify;
use Spider\Commands\Bag;
use Spider\Commands\Command;
use Spider\Commands\Languages\OrientSQL\CommandProcessor;

class OrientSqlProcessorTest extends BaseTestSuite
{
   /* Implemented Methods */
    public function processor()
    {
        return new CommandProcessor();
    }

    /**
     * Returns a command for the the Bag tested in
     * testInsert:it processes a simple insert bag
     */
    public function insertSimple()
    {
        $query = 'INSERT INTO target';

        /* ToDo: insert data should be produced dynamically */
        /* In order to produce this on the fly, we had to copy processor methods */
        /* This seemed to defeat the purpose of testing those methods */
        $query .= " (one, two, three) VALUES (1, 'two', false)";

        $query .= ' RETURN @this';

        $command = new Command($query);
        $command->setScriptLanguage('orientSQL');
        $expected = $command;

        return $expected;
    }

    /**
     * Returns a command for the the Bag tested in
     * testInsert:it processes a simple insert bag
     */
    public function insertMultiple()
    {
        $query = "INSERT INTO target ";
        $query .= "(name, role, ship, husband, past)";
        $query .= " VALUES ";
        $query .= "('mal', 'captain', 'firefly', null, null), ";
        $query .= "('zoe', 'first', null, 'wash', null), ";
        $query .= "('book', 'shepherd', null, null, 'unknown')";
        $query .= " RETURN @this";

        $command = new Command($query);
        $command->setScriptLanguage('orientSQL');
        $expected = $command;

        return $expected;
    }

    /**
     * Returns a command for the the Bag tested in
     * testUpdate:it processes a simple update bag
     */
    public function updateSimple()
    {
        $query = 'UPDATE #12:1';
        $query .= ' MERGE ' . json_encode($this->getData());
        $query .= ' RETURN AFTER';

        $command = new Command($query);
        $command->setScriptLanguage('orientSQL');
        return $command;
    }

    /**
     * Returns a command for the the Bag tested in
     * testUpdate:it processes a complex update bag
     */
    public function updateComplex()
    {
        $query = 'UPDATE target';
        $query .= ' MERGE ' . json_encode($this->getData());
        $query .= $this->getWhereSql();
        $query .= ' LIMIT 10';
        $query .= ' RETURN AFTER';

        $command = new Command($query);
        $command->setScriptLanguage('orientSQL');
        return $command;
    }

    /**
     * Returns a command for the the Bag tested in
     * testDelete:it processes a simple delete bag
     */
    public function deleteSimple()
    {
        $query = 'DELETE VERTEX #12:1';

        $command = new Command($query);
        $command->setScriptLanguage('orientSQL');
        return $command;
    }

    /**
     * Returns a command for the the Bag tested in
     * testDelete:it processes a complex delete bag
     */
    public function deleteComplex()
    {
        $query = 'DELETE VERTEX FROM target';
        $query .= $this->getWhereSql();
        $query .= ' LIMIT 10';

        $command = new Command($query);
        $command->setScriptLanguage('orientSQL');
        return $command;
    }

    /**
     * Returns a command for the the Bag tested in
     * testSelect:it processes a simple select bag
     */
    public function selectSimple()
    {
        $query = 'SELECT';
        $query .= ' FROM target';

        $command = new Command($query);
        $command->setScriptLanguage('orientSQL');
        return $command;
    }

    /**
     * Returns a command for the the Bag tested in
     * testSelect:it processes a select bag with here constraints
     */
    public function selectConstraints()
    {
        $query = 'SELECT';
        $query .= ' FROM target';
        $query .= $this->getWhereSql();

        $command = new Command($query);
        $command->setScriptLanguage('orientSQL');
        return $command;
    }

    /**
     * Returns a command for the the Bag tested in
     * testSelect:it processes a complex select bag
     */
    public function selectOrderBy()
    {
        $query = 'SELECT field1, field2';
        $query .= ' FROM target';
        $query .= $this->getWhereSql();
        $query .= ' ORDER BY field1 DESC';
        $query .= ' LIMIT 3';

        $command = new Command($query);
        $command->setScriptLanguage('orientSQL');
        return $command;
    }

    /**
     * Returns a command for the the Bag tested in
     * testSelect:it processes a complex select bag
     */
    public function selectGroupBy()
    {
        $query = 'SELECT';
        $query .= ' FROM target';
        $query .= $this->getWhereSql();
        $query .= ' GROUP BY field1';
        $query .= ' LIMIT 3';

        $command = new Command($query);
        $command->setScriptLanguage('orientSQL');
        return $command;
    }

    /* Orient Specific Tests */
    public function testSelectFromVByDefault()
    {
        $this->specify("it selects from V by default", function () {
            $bag = new Bag();
            $bag->command = Bag::COMMAND_RETRIEVE;
            // equivalent to $builder->select();
            // Issue #41

            $expected  = new Command('SELECT FROM V');
            $expected->setScriptLanguage('orientSQL');

            $actual = $this->processor()->process($bag);
            $this->assertEquals($expected, $actual, 'failed to return expected Command for simple select bag');
        });
    }

    /* Internal */
    protected function getWhereSql()
    {
        return " WHERE one = 'one' AND two > 2 OR three < 3.14 AND four = true";
    }
}
