<?php
namespace Spider\Test\Unit\Commands\Languages;

use Codeception\Specify;
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
        $query .= ' CONTENT ' . json_encode($this->getData());
        $query .= ' RETURN @this';

        $command = new Command($query);
        $command->setScriptLanguage('OrientSQL');
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
        $command->setScriptLanguage('OrientSQL');
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
        $command->setScriptLanguage('OrientSQL');
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
        $command->setScriptLanguage('OrientSQL');
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
        $command->setScriptLanguage('OrientSQL');
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
        $command->setScriptLanguage('OrientSQL');
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
        $command->setScriptLanguage('OrientSQL');
        return $command;
    }

    /**
     * Returns a command for the the Bag tested in
     * testSelect:it processes a complex select bag
     */
    public function selectComplex()
    {
        $query = 'SELECT field1, field2';
        $query .= ' FROM target';
        $query .= $this->getWhereSql();
        $query .= ' GROUP BY groupField';
        $query .= ' ORDER BY orderField DESC';
        $query .= ' LIMIT 3';

        $command = new Command($query);
        $command->setScriptLanguage('OrientSQL');
        return $command;
    }

    /* Internal */
    protected function getWhereSql()
    {
        return " WHERE one = 'one' AND two > 2 OR three < 3.14 AND four = true";
    }
}
