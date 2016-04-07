<?php
namespace Spider\Test\Unit\Commands\Languages;

use Codeception\Specify;
use Spider\Test\Scenarios\AbstractScenario;
use Spider\Test\Scenarios\CreateSingleVertex;
use Spider\Test\Scenarios\CreateVertices;
use Spider\Test\Scenarios\CreateVerticesAndEdge;
use Spider\Test\Scenarios\CreateVerticesAndUpdate;
use Spider\Test\Scenarios\CreateVerticesAndUpdateEmbedded;
use Spider\Test\Scenarios\DeleteVertexByConstraint;
use Spider\Test\Scenarios\DeleteVertexById;
use Spider\Test\Scenarios\DeleteVerticesByConstraints;
use Spider\Test\Scenarios\RetrieveComplexGroup;
use Spider\Test\Scenarios\RetrieveComplexWithProjections;
use Spider\Test\Scenarios\RetrieveEdgeByLabelAndSingleConstraint;
use Spider\Test\Scenarios\RetrieveTwoVerticesAndCreateEdge;
use Spider\Test\Scenarios\RetrieveVertexByLabel;
use Spider\Test\Scenarios\RetrieveVertexBySingleConstraint;
use Spider\Test\Scenarios\RetrieveVerticesByConstraints;
use Spider\Test\Scenarios\UpdateVertexById;
use Spider\Test\Scenarios\UpdateVerticesByConstraints;

/**
 * This is the base tests for all language processors.
 *
 * Each processor test should extend this class and implement the required methods.
 * This ensures that all processors meet the same testing requirements.
 *
 * See the existing processor tests for more information.
 */
abstract class BaseTestSuite extends \PHPUnit_Framework_TestCase
{
    use Specify;

    /* Begin Tests */
    public function testDelete()
    {
        // it deletes (D) a vertex by default using id
        $this->specify(DeleteVertexById::getDescription(), function () {
            $bag = (new DeleteVertexById([
                'id' => $this->getNativeId()
            ]))->getCommandBag();

//            $expected = $this->getExpectedCommand('delete-vertex-id');
            $expected = $this->getDeleteVertexByIdCommand();
            $actual = $this->processor()->process($bag);

            $this->assertEquals($expected, $actual, 'failed to return expected Command for simple select bag');
        });

        // it deletes (D) a vertex explicitly using a constraint"
        $this->specify(DeleteVertexByConstraint::getDescription(), function () {
            $bag = (new DeleteVertexByConstraint())->getCommandBag();
//            $expected = $this->getExpectedCommand('delete-vertex-one-constraint');

            $expected = $this->getDeleteVertexByConstraintCommand();
            $actual = $this->processor()->process($bag);

            $this->assertEquals($expected, $actual, 'failed to return expected Command for simple select bag');
        });

        // it deletes (D) edges by constraints
        /*
        $this->specify(DeleteEdgesByConstraints::getDescription(), function () {
            $bag = (new DeleteEdgesByConstraints())->getCommandBag();
            $expected = $this->getDeleteEdgesByConstraintsCommand();

            $actual = $this->processor()->process($bag);
            $this->assertEquals($expected, $actual, 'failed to return expected Command for simple select bag');
        });
        */

        // it deletes (D) vertices by default by complex constraints
        $this->specify(DeleteVerticesByConstraints::getDescription(), function () {
            $bag = (new DeleteVerticesByConstraints())->getCommandBag();
//            $expected = $this->getExpectedCommand('delete-complex');

            $expected = $this->getDeleteVerticesByConstraintsCommand();
            $actual = $this->processor()->process($bag);

            $this->assertEquals($expected, $actual, 'failed to return expected Command for simple select bag');
        });
    }

    public function testSelect()
    {
        // (R) by a single where constraint and no label
        $this->specify(RetrieveVertexBySingleConstraint::getDescription(), function () {
            $bag = (new RetrieveVertexBySingleConstraint())->getCommandBag();
//            $expected = $this->getExpectedCommand('select-no-label-one-constraint');

            $expected = $this->getRetrieveVertexBySingleConstraintCommand();
            $actual = $this->processor()->process($bag);

            $this->assertEquals($expected, $actual, 'failed to return expected Command');
        });

        // (R) by type and label
        $this->specify(RetrieveVertexByLabel::getDescription(), function () {
            $bag = (new RetrieveVertexByLabel())->getCommandBag();
//            $expected = $this->getExpectedCommand('select-simple');

            $expected = $this->getRetrieveVertexByLabelCommand();
            $actual = $this->processor()->process($bag);

            $this->assertEquals($expected, $actual, 'failed to return expected Command for simple select bag');
        });

        // (R) EDGE by label, type, and single where
        $this->specify(RetrieveEdgeByLabelAndSingleConstraint::getDescription(), function () {
            $bag = (new RetrieveEdgeByLabelAndSingleConstraint())->getCommandBag();

//            $expected = $this->getExpectedCommand('select-simple-edge');
            $expected = $this->getRetrieveEdgeByLabelAndSingleConstraintCommand();
            $actual = $this->processor()->process($bag);

            $this->assertEquals($expected, $actual, 'failed to return expected Command for simple select bag');
        });

        // @todo do the same as above for both (requires traversals for neo

        // (R) by label, type, and multiple wheres
        $this->specify(RetrieveVerticesByConstraints::getDescription(), function () {
            $bag = (new RetrieveVerticesByConstraints())->getCommandBag();

//            $expected = $this->getExpectedCommand('select-constraints');
            $expected = $this->getRetrieveVerticesByConstraintsCommand();
            $actual = $this->processor()->process($bag);

            $this->assertEquals($expected, $actual, 'failed to return expected Command for simple select bag');
        });

        // (R) projections by label, type, and wheres - orders and limits
        $this->specify(RetrieveComplexWithProjections::getDescription(), function () {
            $bag = (new RetrieveComplexWithProjections())->getCommandBag();

//            $expected = $this->getExpectedCommand('select-order-by');
            $expected = $this->getRetrieveComplexWithProjectionsCommand();
            $actual = $this->processor()->process($bag);

            $this->assertEquals($expected, $actual, 'failed to return expected Command');
        });

        // (R) by label, type, and wheres - groups and limits
        $this->specify(RetrieveComplexGroup::getDescription(), function () {
            $bag = (new RetrieveComplexGroup())->getCommandBag();

//            $expected = $this->getExpectedCommand('select-group-by');
            $expected = $this->getRetrieveComplexGroupCommand();
            $actual = $this->processor()->process($bag);

            $this->assertEquals($expected, $actual, 'failed to return expected Command');
        });
    }

    public function testInsert()
    {
        // it inserts (C) a single vertex
        $this->specify(CreateSingleVertex::getDescription(), function () {
            $bag = (new CreateSingleVertex())->getCommandBag();

//            $expected = $this->getExpectedCommand('insert-simple');
            $expected = $this->getCreateSingleVertexCommand();
            $actual = $this->processor()->process($bag);

            $this->assertEquals($expected, $actual, 'failed to return expected Command for simple select bag');
        });

        // it inserts (C) multiple vertices
        $this->specify(CreateVertices::getDescription(), function () {
            $bag = (new CreateVertices())->getCommandBag();

//            $expected = $this->getExpectedCommand('insert-multiple');
            $expected = $this->getCreateVerticesCommand();
            $actual = $this->processor()->process($bag);

            $this->assertEquals($expected, $actual, 'failed to return expected Command for multiple insert');
        });
    }

    /* Scenario Tests */
    public function testCreateEdges()
    {
        // finds (R) two vertices and creates (C) an edge in between
        $this->specify(RetrieveTwoVerticesAndCreateEdge::getDescription(), function () {
            $bag = (new RetrieveTwoVerticesAndCreateEdge())->getCommandBag();

//            $expected = $this->getExpectedCommand('find-two-vertices-and-create-edge');
            $expected = $this->getRetrieveTwoVerticesAndCreateEdgeCommand();
            $actual = $this->processor()->process($bag);

            $this->assertEquals($expected, $actual, 'failed to return expected Command for simple select bag');
        });

        // it creates (C) two vertices and creates (C) an edge in between (R)
        $this->specify(CreateVerticesAndEdge::getDescription(), function () {
            $bag = (new CreateVerticesAndEdge())->getCommandBag();

//            $expected = $this->getExpectedCommand('create-two-vertices-and-create-edge');
            $expected = $this->getCreateVerticesAndEdgeCommand();
            $actual = $this->processor()->process($bag);

            $this->assertEquals($expected, $actual, 'failed to return expected Command for simple select bag');
        });
    }

    public function testUpdate()
    {
        // it updates (U) vertices by ID
        $this->specify(UpdateVertexById::getDescription(), function () {
            $bag = (new UpdateVertexById(['id' => $this->getNativeId()]))->getCommandBag();

//            $expected = $this->getExpectedCommand('update-simple');
            $expected = $this->getUpdateVertexByIdCommand();
            $actual = $this->processor()->process($bag);

            $this->assertEquals($expected, $actual, 'failed to return expected Command for simple select bag');
        });

        // it updates (U) vertices by complex constraints
        $this->specify(UpdateVerticesByConstraints::getDescription(), function () {
            $bag = (new UpdateVerticesByConstraints())->getCommandBag();

//            $expected = $this->getExpectedCommand('update-complex');
            $expected = $this->getUpdateVerticesByConstraintsCommand();
            $actual = $this->processor()->process($bag);

            $this->assertEquals($expected, $actual, 'failed to return expected Command for simple select bag');
        });

        // it creates (C) vertices and updates (U) them with data
        $this->specify(CreateVerticesAndUpdate::getDescription(), function () {
            $bag = (new CreateVerticesAndUpdate())->getCommandBag();

//            $expected = $this->getExpectedCommand('create-and-update');
            $expected = $this->getCreateVerticesAndUpdateCommand();
            $actual = $this->processor()->process($bag);

            $this->assertEquals($expected, $actual, 'failed to return expected Command for simple select bag');
        });

        // it creates (C) vertices and updates (U) using an embedded retrieve (R)
        $this->specify(CreateVerticesAndUpdateEmbedded::getDescription(), function () {
            $bag = (new CreateVerticesAndUpdateEmbedded())->getCommandBag();

//            $expected = $this->getExpectedCommand('create-and-update');
            $expected = $this->getCreateVerticesAndUpdateEmbeddedCommand();
            $actual = $this->processor()->process($bag);

            $this->assertEquals($expected, $actual, 'failed to return expected Command for simple select bag');
        });
    }

    /*
    public function testScenarios()
    {
        $this->specify(
            "it find (R) existing vertices and creates (C) an edge between them then updates (U) that edge", function () {
            $bag = (new RetrieveExistingVerticesCreateEdgeUpdateEdge())->getCommandBag();

            // $expected = $this->getExpectedCommand('find-vertices-create-edge-update');
            $expected = getRetrieveExistingVerticesCreateEdgeUpdateEdgeCommand();
            $actual = $this->processor()->process($bag);

            $this->assertEquals($expected, $actual, 'failed to return expected Command for simple select bag');
        });
    }
    */

    /* Internals */
    public function getData()
    {
        return AbstractScenario::getData();
    }

    /* Methods to Implement */
    /**
     * Returns a valid CommandProcessor
     * @return \Spider\Commands\Languages\ProcessorInterface
     */
    abstract public function processor();

    /** Returns a Valid ID formatted as the language requires */
    abstract public function getNativeId();

    /**
     * Returns a valid Command for the `CreateSingleVertex` Scenario
     * @return \Spider\Commands\Command
     */
    abstract public function getCreateSingleVertexCommand();

    /**
     * Returns a valid Command for the `CreateVertices` Scenario
     * @return \Spider\Commands\Command
     */
    abstract public function getCreateVerticesCommand();

    /**
     * Returns a valid Command for the `UpdateVertexById` Scenario
     * @return \Spider\Commands\Command
     */
    abstract public function getUpdateVertexByIdCommand();

    /**
     * Returns a valid Command for the `UpdateVerticesByConstraints` Scenario
     * @return \Spider\Commands\Command
     */
    abstract public function getUpdateVerticesByConstraintsCommand();

    /**
     * Returns a valid Command for the `CreateVerticesAndUpdate` Scenario
     * @return \Spider\Commands\Command
     */
    abstract public function getCreateVerticesAndUpdateCommand();

    /**
     * Returns a valid Command for the `CreateVerticesAndUpdateEmbedded` Scenario
     * @return \Spider\Commands\Command
     */
    abstract public function getCreateVerticesAndUpdateEmbeddedCommand();

    /**
     * Returns a valid Command for the `DeleteVertexById` Scenario
     * @return \Spider\Commands\Command
     */
    abstract public function getDeleteVertexByIdCommand();

    /**
     * Returns a valid Command for the `DeleteVertexByConstraint` Scenario
     * @return \Spider\Commands\Command
     */
    abstract public function getDeleteVertexByConstraintCommand();

    /**
     * Returns a valid Command for the `DeleteVerticesByConstraints` Scenario
     * @return \Spider\Commands\Command
     */
    abstract public function getDeleteVerticesByConstraintsCommand();

    /**
     * Returns a valid Command for the `RetrieveVertexByLabel` Scenario
     * @return \Spider\Commands\Command
     */
    abstract public function getRetrieveVertexByLabelCommand();

    /**
     * Returns a valid Command for the `RetrieveEdgeByLabelAndSingleConstraint` Scenario
     * @return \Spider\Commands\Command
     */
    abstract public function getRetrieveEdgeByLabelAndSingleConstraintCommand();

    /**
     * Returns a valid Command for the `RetrieveVerticesByConstraints` Scenario
     * @return \Spider\Commands\Command
     */
    abstract public function getRetrieveVerticesByConstraintsCommand();

    /**
     * Returns a valid Command for the `RetrieveComplexWithProjections` Scenario
     * @return \Spider\Commands\Command
     */
    abstract public function getRetrieveComplexWithProjectionsCommand();

    /**
     * Returns a valid Command for the `RetrieveComplexGroup` Scenario
     * @return \Spider\Commands\Command
     */
    abstract public function getRetrieveComplexGroupCommand();

    /**
     * Returns a valid Command for the `RetrieveVertexBySingleConstraint` Scenario
     * @return \Spider\Commands\Command
     */
    abstract public function getRetrieveVertexBySingleConstraintCommand();

    /**
     * Returns a valid Command for the `RetrieveTwoVerticesAndCreateEdge` Scenario
     * @return \Spider\Commands\Command
     */
    abstract public function getRetrieveTwoVerticesAndCreateEdgeCommand();

    /**
     * Returns a valid Command for the `CreateVerticesAndEdge` Scenario
     * @return \Spider\Commands\Command
     */
    abstract public function getCreateVerticesAndEdgeCommand();

    /**
     * Returns a valid Command for the `RetrieveExistingVerticesCreateEdgeUpdateEdge` Scenario
     * @return \Spider\Commands\Command
     */
    abstract public function getRetrieveExistingVerticesCreateEdgeUpdateEdgeCommand();
}
