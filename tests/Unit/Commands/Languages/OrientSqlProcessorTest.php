<?php
namespace Spider\Test\Unit\Commands\Languages;

use Codeception\Specify;
use Spider\Commands\Command;
use Spider\Commands\Languages\OrientSQL\CommandProcessor;

class OrientSqlProcessorTest extends BaseTestSuite
{
    /* Implemented Methods */
    /**
     * Returns a valid CommandProcessor
     * @return \Spider\Commands\Languages\ProcessorInterface
     */
    public function processor()
    {
        return new CommandProcessor();
    }

    /** Returns a Valid ID formatted as the language requires */
    public function getNativeId()
    {
        return "#12:3";
    }

    /**
     * Returns a valid Command for the `CreateSingleVertex` Scenario
     * @return \Spider\Commands\Command
     */
    public function getCreateSingleVertexCommand()
    {
        $query = $this->getBatchOpening();
        $query .= 'LET c1 = CREATE VERTEX person CONTENT {"name":"michael"}';
        $query .= $this->getCommit();
        $query .= 'return $c1';

        return new Command($query, 'orientSQL');
    }

    /**
     * Returns a valid Command for the `CreateVertices` Scenario
     * @return \Spider\Commands\Command
     */
    public function getCreateVerticesCommand()
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
     * Returns a valid Command for the `UpdateVertexById` Scenario
     * @return \Spider\Commands\Command
     */
    public function getUpdateVertexByIdCommand()
    {
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
     * Returns a valid Command for the `UpdateVerticesByConstraints` Scenario
     * @return \Spider\Commands\Command
     */
    public function getUpdateVerticesByConstraintsCommand()
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
     * Returns a valid Command for the `CreateVerticesAndUpdate` Scenario
     * @return \Spider\Commands\Command
     */
    public function getCreateVerticesAndUpdateCommand()
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
        $query .= 'return [$c1,$c2,$u3]';

        $command = new Command($query);
        $command->setScriptLanguage('orientSQL');
        return $command;
    }

    /**
     * Returns a valid Command for the `CreateVerticesAndUpdateEmbedded` Scenario
     * @return \Spider\Commands\Command
     */
    public function getCreateVerticesAndUpdateEmbeddedCommand()
    {
        return $this->getCreateVerticesAndupdateCommand();
    }

    /**
     * Returns a valid Command for the `DeleteVertexById` Scenario
     * @return \Spider\Commands\Command
     */
    public function getDeleteVertexByIdCommand()
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
     * Returns a valid Command for the `DeleteVertexByConstraint` Scenario
     * @return \Spider\Commands\Command
     */
    public function getDeleteVertexByConstraintCommand()
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
     * Returns a valid Command for the `DeleteVerticesByConstraints` Scenario
     * @return \Spider\Commands\Command
     */
    public function getDeleteVerticesByConstraintsCommand()
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
     * testSelect:it processes a simple select bag
     */
    public function getRetrieveVertexByLabelCommand()
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
    public function getRetrieveEdgeByLabelAndSingleConstraintCommand()
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
    public function getRetrieveVerticesByConstraintsCommand()
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
    public function getRetrieveComplexWithProjectionsCommand()
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
    public function getRetrieveComplexGroupCommand()
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

    public function getRetrieveVertexBySingleConstraintCommand()
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
    public function getRetrieveTwoVerticesAndCreateEdgeCommand()
    {
        $query = $this->getBatchOpening();
        $query .= "LET c1 = CREATE EDGE knows FROM (SELECT FROM V WHERE name = 'josh') TO (SELECT FROM V WHERE name = 'peter')";
        $query .= $this->getCommit();
        $query .= 'return $c1';

        $command = new Command($query);
        $command->setScriptLanguage('orientSQL');
        return $command;
    }

    public function getCreateVerticesAndEdgeCommand()
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

    public function getRetrieveExistingVerticesCreateEdgeUpdateEdgeCommand()
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

    /**
     * Returns a valid Command for the `DeleteEdgesByConstraints` Scenario
     * @return \Spider\Commands\Command
     */
    public function getDeleteEdgesByConstraintsCommand()
    {
        // TODO: Implement getDeleteEdgesByConstraintsCommand() method.
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
}
