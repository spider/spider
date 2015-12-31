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
        $query = $this->getBatchOpening();
        $query .= 'LET c1 = CREATE VERTEX person CONTENT {"name":"michael"}';
        $query .= $this->getCommit();
        $query .= 'return $c1';

        return new Command($query, 'orientSQL');
    }

    /**
     * Returns a command for the the Bag tested in
     * testInsert:it processes a simple insert bag
     */
    public function insertMultiple()
    {
        $query = $this->getBatchOpening();
        $query .= 'LET c1 = CREATE VERTEX person CONTENT {"name":"michael"}'."\n";
        $query .= 'LET c2 = CREATE VERTEX target CONTENT {"name":"dylan"}'."\n";
        $query .= 'LET c3 = CREATE VERTEX CONTENT {"name":"peter"}';
        $query .= $this->getCommit();
        $query .= 'return [$c1,$c2,$c3]';

        return new Command($query, 'orientSQL');
    }

    /**
     * Returns a command for the the Bag tested in
     * testUpdate:it processes a simple update bag
     */
    public function updateSimple()
    {
        /* ToDo: Update this to use UPDATE #12:0 format when an id is given */
        $query = $this->getBatchOpening();
        $query .= 'LET u1 = UPDATE V MERGE '.json_encode($this->getData());
        $query .= ' RETURN AFTER WHERE @rid = ' . $this->getNativeId();
        $query .= $this->getCommit();
        $query .= 'return $u1';

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
        $query = $this->getBatchOpening();
        $query .= 'LET u1 = UPDATE target';
        $query .= ' MERGE ' . json_encode($this->getData());
        $query .= ' RETURN AFTER';
        $query .= $this->getWhereSql();
        $query .= ' LIMIT 10';
        $query .= $this->getCommit();
        $query .= 'return $u1';

        $command = new Command($query);
        $command->setScriptLanguage('orientSQL');
        return $command;
    }

    /**
     * Returns a command for the the Bag tested in
     * testUpdate:it processes a complex update bag
     */
    public function createAndUpdate()
    {
        $query = $this->getBatchOpening();
        $query .= 'LET c1 = CREATE VERTEX person CONTENT {"name":"michael"}'."\n";
        $query .= 'LET c2 = CREATE VERTEX person CONTENT {"name":"dylan"}'."\n";
        $query .= 'LET u3 = UPDATE person';
        $query .= ' MERGE ' . json_encode($this->getData());
        $query .= ' RETURN AFTER';
        $query .= ' WHERE name = \'michael\'';
        $query .= ' LIMIT 15';
        $query .= $this->getCommit();
        $query .= 'return [$c1,$c2]';

        $command = new Command($query);
        $command->setScriptLanguage('orientSQL');
        return $command;
    }

    /**
     * Returns a command for the the Bag tested in
     * testDelete:it processes a simple delete bag
     */
    public function deleteVertexId()
    {
        $query = $this->getBatchOpening();
        $query .= "LET d1 = DELETE VERTEX V WHERE @rid = ".$this->getNativeId();
        $query .= $this->getCommit();
        $query .= 'return $d1';

        $command = new Command($query);
        $command->setScriptLanguage('orientSQL');
        return $command;
    }

    /**
     * Returns a command for the the Bag tested in
     * testDelete:it processes a simple delete bag
     */
    public function deleteVertexOneConstraint()
    {
        $query = $this->getBatchOpening();
        $query .= "LET d1 = DELETE VERTEX V WHERE name = 'marko' LIMIT 1";
        $query .= $this->getCommit();
        $query .= 'return $d1';

        $command = new Command($query);
        $command->setScriptLanguage('orientSQL');
        return $command;
    }

    /**
     * Returns a command for the the Bag tested in
     * testDelete:it processes a simple delete bag
     */
//    public function deleteEdges()
//    {
//        $query = $this->getBatchOpening();
//        $query .= "DELETE EDGE label " . $this->getWhereSql();
//        $query .= $this->getCommit();
//
//        $command = new Command($query);
//        $command->setScriptLanguage('orientSQL');
//        return $command;
//    }

    /**
     * Returns a command for the the Bag tested in
     * testDelete:it processes a complex delete bag
     */
    public function deleteComplex()
    {
        $query = $this->getBatchOpening();
        $query .= 'LET d1 = DELETE VERTEX label';
        $query .= $this->getWhereSql();
        $query .= ' LIMIT 10';
        $query .= $this->getCommit();
        $query .= 'return $d1';

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
        $query = $this->getBatchOpening();
        $query .= 'LET s1 = SELECT';
        $query .= ' FROM target';
        $query .= $this->getCommit();
        $query .= 'return $s1';

        $command = new Command($query);
        $command->setScriptLanguage('orientSQL');
        return $command;
    }

    /**
     * Returns a command for the the Bag tested in
     * testSelect:it processes a simple select bag for edges
     */
    public function selectSimpleEdge()
    {
        $query = $this->getBatchOpening();
        $query .= 'LET s1 = SELECT';
        $query .= ' FROM target';
        $query .= $this->getCommit();
        $query .= 'return $s1';

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
        $query = $this->getBatchOpening();
        $query .= 'LET s1 = SELECT';
        $query .= ' FROM target';
        $query .= $this->getWhereSql();
        $query .= $this->getCommit();
        $query .= 'return $s1';

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
        $query = $this->getBatchOpening();
        $query .= 'LET s1 = SELECT field1, field2';
        $query .= ' FROM target';
        $query .= $this->getWhereSql();
        $query .= ' ORDER BY field1 DESC';
        $query .= ' LIMIT 3';
        $query .= $this->getCommit();
        $query .= 'return $s1';

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
        $query = $this->getBatchOpening();
        $query .= 'LET s1 = SELECT';
        $query .= ' FROM target';
        $query .= $this->getWhereSql();
        $query .= ' GROUP BY field1';
        $query .= ' LIMIT 3';
        $query .= $this->getCommit();
        $query .= 'return $s1';

        $command = new Command($query);
        $command->setScriptLanguage('orientSQL');
        return $command;
    }

    public function selectNoLabelOneConstraint()
    {
        $query = $this->getBatchOpening();
        $query .= "LET s1 = SELECT FROM V WHERE name = 'josh'";
        $query .= $this->getCommit();
        $query .= 'return $s1';

        $command = new Command($query);
        $command->setScriptLanguage('orientSQL');
        return $command;
    }

    /* Scenarios */
    public function findTwoVerticesAndCreateEdge()
    {
        $query = $this->getBatchOpening();
        $query .= "LET c1 = CREATE EDGE knows FROM (SELECT FROM V WHERE name = 'josh') TO (SELECT FROM V WHERE name = 'peter')";
        $query .= $this->getCommit();
        $query .= 'return $c1';

        $command = new Command($query);
        $command->setScriptLanguage('orientSQL');
        return $command;
    }

    public function createTwoVerticesAndCreateEdge()
    {
        $query = $this->getBatchOpening();
        $query .= 'LET c1 = CREATE VERTEX person CONTENT {"name":"michael"}'."\n";
        $query .= 'LET c2 = CREATE VERTEX person CONTENT {"name":"dylan"}'."\n";
        $query .= "LET c3 = CREATE EDGE knows FROM (SELECT FROM V WHERE name = 'dylan') TO (SELECT FROM V WHERE name = 'michael')";
        $query .= $this->getCommit();
        $query .= 'return [$c1,$c2,$c3]';

        $command = new Command($query);
        $command->setScriptLanguage('orientSQL');
        return $command;
    }

    public function findVerticesCreateEdgeUpdate()
    {
        $query = $this->getBatchOpening();
        $query .= 'LET c1 = CREATE EDGE knows FROM (SELECT FROM V WHERE name = \'josh\') TO (SELECT FROM V WHERE name = \'peter\')'."\n";
        $query .= 'LET u2 = UPDATE $t1 MERGE '. json_encode($this->getData()) .' RETURN AFTER';
        $query .= $this->getCommit();
        $query .= 'return [$c1,$c2,$u3]';

        $command = new Command($query);
        $command->setScriptLanguage('orientSQL');
        return $command;
    }

    /* Orient Specific Tests */
//    public function testSelectFromVByDefault()
//    {
//        $this->specify("it selects from V by default", function () {
//            $bag = new Bag();
//            $bag->command = Bag::COMMAND_RETRIEVE;
//            // equivalent to $builder->select();
//            // Issue #41
//
//            $query = $this->getBatchOpening();
//            $query .= 'SELECT';
//            $query .= ' FROM V';
//            $query .= $this->getCommit();
//
//            $expected = new Command($query);
//            $expected->setScriptLanguage('orientSQL');
//
//            $actual = $this->processor()->process($bag);
//            $this->assertEquals($expected, $actual, 'failed to return expected Command for simple select bag');
//        });
//    }

    /* Internal */
    protected function getWhereSql()
    {
        return " WHERE one = 'one' AND two > 2 OR three < 3.14 AND four = true";
    }

    protected function getBatchOpening()
    {
        return "begin\n";
    }

    protected function getCommit()
    {
        return "\ncommit retry 100\n";
    }

    public function getNativeId()
    {
        return "#12:3";
    }
}
