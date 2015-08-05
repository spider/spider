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

    public function updateSimple()
    {
        $query = 'UPDATE #12:1';
        $query .= ' MERGE ' . json_encode($this->getData());
        $query .= ' RETURN AFTER';

        $command = new Command($query);
        $command->setScriptLanguage('OrientSQL');
        return $command;
    }

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

    public function deleteSimple()
    {
        $query = 'DELETE VERTEX #12:1';

        $command = new Command($query);
        $command->setScriptLanguage('OrientSQL');
        return $command;
    }

    public function deleteComplex()
    {
        $query = 'DELETE VERTEX FROM target';
        $query .= $this->getWhereSql();
        $query .= ' LIMIT 10';

        $command = new Command($query);
        $command->setScriptLanguage('OrientSQL');
        return $command;
    }

    public function selectSimple()
    {
        $query = 'SELECT';
        $query .= ' FROM target';

        $command = new Command($query);
        $command->setScriptLanguage('OrientSQL');
        $command->setRw('read');
        return $command;
    }

    public function selectConstraints()
    {
        $query = 'SELECT';
        $query .= ' FROM target';
        $query .= $this->getWhereSql();

        $command = new Command($query);
        $command->setScriptLanguage('OrientSQL');
        $command->setRw('read');
        return $command;
    }

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
        $command->setRw('read');
        return $command;
    }

    /* Internal */
    protected function getWhereSql()
    {
        return " WHERE one = 'one' AND two > 2 OR three < 3.14 AND four = true";
    }
}
