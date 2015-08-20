<?php
namespace Spider\Test\Unit\Commands\Languages;

use Codeception\Specify;
use Spider\Commands\Bag;
use Spider\Commands\Command;
use Spider\Commands\Languages\Cypher\CommandProcessor;

class CypherProcessorTest extends BaseTestSuite
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
        $query = 'CREATE (spider_a)';

        /* ToDo: insert data should be produced dynamically */
        /* In order to produce this on the fly, we had to copy processor methods */
        /* This seemed to defeat the purpose of testing those methods */
        $query .= " SET spider_a.one = 1, spider_a.two = 'two', spider_a.three = false, spider_a :target";

        $query .= ' RETURN spider_a';

        $command = new Command($query);
        $command->setScriptLanguage('cypher');
        $expected = $command;

        return $expected;
    }

    /**
     * Returns a command for the the Bag tested in
     * testInsert:it processes a simple insert bag
     */
    public function insertMultiple()
    {
        $query = "CREATE (spider_a), (spider_b), (spider_c)";
        $query .= " SET spider_a.name = 'mal', spider_a.role = 'captain', spider_a.ship = 'firefly', spider_a :target,";
        $query .= " spider_b.name = 'zoe', spider_b.role = 'first', spider_b.husband = 'wash', spider_b :target,";
        $query .= " spider_c.name = 'book', spider_c.role = 'shepherd', spider_c.past = 'unknown', spider_c :target";
        $query .= " RETURN spider_a, spider_b, spider_c";

        $command = new Command($query);
        $command->setScriptLanguage('cypher');
        $expected = $command;

        return $expected;
    }

    /**
     * Returns a command for the the Bag tested in
     * testUpdate:it processes a simple update bag
     */
    public function updateSimple()
    {
        $query = 'MATCH (spider_a)';
        $query .= ' WHERE ID(spider_a) = target_id';
        $query .= ' SET ';
        $data = [];
        foreach($this->getData() as $key => $value)
        {
            switch(true) {
                case $value === false:
                    $value = 'false';
                    break;
                case $value === true:
                    $value = 'true';
                    break;
                case is_string($value) && !is_int($value):
                    $value = "'{$value}'";
            }
            $data[] = 'spider_a.'.$key.' = '.$value;
        }
        $query .= implode(', ', $data);
        $command = new Command($query);
        $command->setScriptLanguage('cypher');
        return $command;
    }

    /**
     * Returns a command for the the Bag tested in
     * testUpdate:it processes a complex update bag
     */
    public function updateComplex()
    {
        $query = 'MATCH (spider_a)';
        $query .= $this->getWhereSql().' AND spider_a:target';
        $query .= ' LIMIT 10';

        $query .= ' SET ';
        $data = [];
        foreach($this->getData() as $key => $value)
        {
            switch(true) {
                case $value === false:
                    $value = 'false';
                    break;
                case $value === true:
                    $value = 'true';
                    break;
                case is_string($value) && !is_int($value):
                    $value = "'{$value}'";
            }
            $data[] = 'spider_a.'.$key.' = '.$value;
        }
        $query .= implode(', ', $data);
        $command = new Command($query);
        $command->setScriptLanguage('cypher');
        return $command;
    }

    /**
     * Returns a command for the the Bag tested in
     * testDelete:it processes a simple delete bag
     */
    public function deleteSimple()
    {
        $query = 'MATCH (spider_a) WHERE ID(spider_a) = target_id DELETE spider_a';

        $command = new Command($query);
        $command->setScriptLanguage('cypher');
        return $command;
    }

    /**
     * Returns a command for the the Bag tested in
     * testDelete:it processes a complex delete bag
     */
    public function deleteComplex()
    {
        $query = 'MATCH (spider_a)';
        $query .= $this->getWhereSql(). ' AND spider_a:target';
        $query .= ' LIMIT 10';
        $query .= ' DELETE spider_a';

        $command = new Command($query);
        $command->setScriptLanguage('cypher');
        return $command;
    }

    /**
     * Returns a command for the the Bag tested in
     * testSelect:it processes a simple select bag
     */
    public function selectSimple()
    {
        $query = 'MATCH (spider_a) WHERE spider_a:target RETURN spider_a';

        $command = new Command($query);
        $command->setScriptLanguage('cypher');
        return $command;
    }

    /**
     * Returns a command for the the Bag tested in
     * testSelect:it processes a simple select bag for edges
     */
    public function selectSimpleEdge()
    {
        $query = 'MATCH ()-[spider_a]-() WHERE spider_a:target RETURN spider_a';

        $command = new Command($query);
        $command->setScriptLanguage('cypher');
        return $command;
    }

    /**
     * Returns a command for the the Bag tested in
     * testSelect:it processes a select bag with here constraints
     */
    public function selectConstraints()
    {
        $query = 'MATCH (spider_a)';
        $query .= $this->getWhereSql().' AND spider_a:target';
        $query .= ' RETURN spider_a';

        $command = new Command($query);
        $command->setScriptLanguage('cypher');
        return $command;
    }

    /**
     * Returns a command for the the Bag tested in
     * testSelect:it processes a complex select bag
     */
    public function selectComplex()
    {
        $query = 'MATCH (spider_a) RETURN spider_a.field1, spider_a.field2';
        $query .= $this->getWhereSql().' AND spider_a:target';
        $query .= ' ORDER BY spider_a.field1 DESC';
        $query .= ' LIMIT 3';

        $command = new Command($query);
        $command->setScriptLanguage('cypher');
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

            $expected  = new Command('MATCH (spider_a) RETURN spider_a');
            $expected->setScriptLanguage('cypher');

            $actual = $this->processor()->process($bag);
            $this->assertEquals($expected, $actual, 'failed to return expected Command for simple select bag');
        });
    }

    /**
     * Returns a command for the the Bag tested in
     * testSelect:it processes a complex select bag
     */
    public function selectOrderBy()
    {
        $query = 'MATCH (spider_a)';
        $query .= $this->getWhereSql().' AND spider_a:target';
        $query .= ' ORDER BY spider_a.field1 DESC';
        $query .= ' RETURN spider_a.field1, spider_a.field2';
        $query .= ' LIMIT 3';

        $command = new Command($query);
        $command->setScriptLanguage('cypher');
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

    /* Internal */
    protected function getWhereSql()
    {
        return " WHERE spider_a.one = 'one' AND spider_a.two > 2 OR spider_a.three < 3.14 AND spider_a.four = true";
    }
}
