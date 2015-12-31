<?php
namespace Spider\Test\Unit\Commands\Languages;

use Codeception\Specify;
use Spider\Commands\Bag;
use Spider\Test\Scenarios\CreateSingleVertex;
use Spider\Test\Scenarios\CreateVertices;
use Spider\Test\Scenarios\CreateVerticesAndEdge;
use Spider\Test\Scenarios\CreateVerticesAndupdate;
use Spider\Test\Scenarios\CreateVerticesAndUpdateEmbedded;
use Spider\Test\Scenarios\DeleteVertexByConstraint;
use Spider\Test\Scenarios\DeleteVertexById;
use Spider\Test\Scenarios\DeleteVerticesByConstraints;
use Spider\Test\Scenarios\RetrieveByTypeAndLabel;
use Spider\Test\Scenarios\RetrieveComplexGroup;
use Spider\Test\Scenarios\RetrieveComplexWithProjections;
use Spider\Test\Scenarios\RetrieveEdgeByLabelAndSingleConstraint;
use Spider\Test\Scenarios\RetrieveEdgeByLableAndSingleConstraint;
use Spider\Test\Scenarios\RetrieveTwoVerticesAndCreateEdge;
use Spider\Test\Scenarios\RetrieveVertexByConstraint;
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
    /* Simple Tests */
    public function testDelete()
    {
        $this->specify("it deletes (D) a vertex by default using id", function () {
            $bag = (new DeleteVertexById([
                'id' => $this->getNativeId()
            ]))->getCommandBag();

            $expected = $this->getExpectedCommand('delete-vertex-id');

            $actual = $this->processor()->process($bag);
            $this->assertEquals($expected, $actual, 'failed to return expected Command for simple select bag');
        });

        $this->specify("it deletes (D) a vertex explicitly using a constraint", function () {
            $bag = (new DeleteVertexByConstraint())->getCommandBag();
            $expected = $this->getExpectedCommand('delete-vertex-one-constraint');

            $actual = $this->processor()->process($bag);
            $this->assertEquals($expected, $actual, 'failed to return expected Command for simple select bag');
        });

//        $this->specify("it deletes (D) edges by constraints", function () {
//            $bag = (new DeleteVertexByConstraints())->getCommandBag();
//            $expected = $this->getExpectedCommand('delete-edges');
//
//            $actual = $this->processor()->process($bag);
//            $this->assertEquals($expected, $actual, 'failed to return expected Command for simple select bag');
//        });

        $this->specify("it deletes (D) vertices by default by complex constraints", function () {
            $bag = (new DeleteVerticesByConstraints())->getCommandBag();
            $expected = $this->getExpectedCommand('delete-complex');

            $actual = $this->processor()->process($bag);
            $this->assertEquals($expected, $actual, 'failed to return expected Command for simple select bag');
        });
    }

    public function testSelect()
    {
        $this->specify("(R) by a single where constraint and no label", function () {
            $bag = (new RetrieveVertexBySingleConstraint())->getCommandBag();
            $expected = $this->getExpectedCommand('select-no-label-one-constraint');

            $actual = $this->processor()->process($bag);
            $this->assertEquals($expected, $actual, 'failed to return expected Command');
        });

        $this->specify("(R) by type and label", function () {
            $bag = (new RetrieveVertexByLabel())->getCommandBag();
            $expected = $this->getExpectedCommand('select-simple');

            $actual = $this->processor()->process($bag);
            $this->assertEquals($expected, $actual, 'failed to return expected Command for simple select bag');
        });

        $this->specify("(R) EDGE by label, type, and single where", function () {
            $bag = (new RetrieveEdgeByLabelAndSingleConstraint())->getCommandBag();
            $expected = $this->getExpectedCommand('select-simple-edge');

            $actual = $this->processor()->process($bag);
            $this->assertEquals($expected, $actual, 'failed to return expected Command for simple select bag');
        });

        // @todo do the same as above for both (requires traversals for neo

        $this->specify("(R) by label, type, and multiple wheres", function () {
            $bag = (new RetrieveVerticesByConstraints())->getCommandBag();
            $expected = $this->getExpectedCommand('select-constraints');

            $actual = $this->processor()->process($bag);
            $this->assertEquals($expected, $actual, 'failed to return expected Command for simple select bag');
        });

        $this->specify("(R) projections by label, type, and wheres - orders and limits", function () {

            $bag = (new RetrieveComplexWithProjections())->getCommandBag();
            $expected = $this->getExpectedCommand('select-order-by');

            $actual = $this->processor()->process($bag);
            $this->assertEquals($expected, $actual, 'failed to return expected Command');
        });

        $this->specify("(R) by label, type, and wheres - groups and limits", function () {
            $bag = (new RetrieveComplexGroup())->getCommandBag();
            $expected = $this->getExpectedCommand('select-group-by');

            $actual = $this->processor()->process($bag);
            $this->assertEquals($expected, $actual, 'failed to return expected Command');
        });
    }

    public function testInsert()
    {
        $this->specify("it inserts (C) a single vertex", function () {
            $bag = (new CreateSingleVertex())->getCommandBag();
            $expected = $this->getExpectedCommand('insert-simple');

            $actual = $this->processor()->process($bag);
            $this->assertEquals($expected, $actual, 'failed to return expected Command for simple select bag');
        });

        $this->specify("it inserts (C) multiple vertices", function () {
            $bag = (new CreateVertices())->getCommandBag();
            $expected = $this->getExpectedCommand('insert-multiple');

            $actual = $this->processor()->process($bag);
            $this->assertEquals($expected, $actual, 'failed to return expected Command for multiple insert');
        });
    }

    /* Scenario Tests */
    public function testCreateEdges()
    {
        $this->specify("finds (R) two vertices and creates (C) an edge in between", function () {
            $bag = (new RetrieveTwoVerticesAndCreateEdge())->getCommandBag();
            $expected = $this->getExpectedCommand('find-two-vertices-and-create-edge');

            $actual = $this->processor()->process($bag);
            $this->assertEquals($expected, $actual, 'failed to return expected Command for simple select bag');
        });

        $this->specify("it creates (C) two vertices and creates (C) an edge in between (R)", function () {
            $bag = (new CreateVerticesAndEdge())->getCommandBag();
            $expected = $this->getExpectedCommand('create-two-vertices-and-create-edge');

            $actual = $this->processor()->process($bag);
            $this->assertEquals($expected, $actual, 'failed to return expected Command for simple select bag');
        });
    }

    public function testUpdate()
    {
        $this->specify("it updates (U) vertices by ID", function () {
            $bag = (new UpdateVertexById(['id' => $this->getNativeId()]))->getCommandBag();
            $expected = $this->getExpectedCommand('update-simple');

            $actual = $this->processor()->process($bag);
            $this->assertEquals($expected, $actual, 'failed to return expected Command for simple select bag');
        });

        $this->specify("it updates (U) vertices by complex constraints", function () {
            $bag = (new UpdateVerticesByConstraints())->getCommandBag();
            $expected = $this->getExpectedCommand('update-complex');

            $actual = $this->processor()->process($bag);
            $this->assertEquals($expected, $actual, 'failed to return expected Command for simple select bag');
        });

        $this->specify("it creates (C) vertices and updates (U) them with data", function () {
            $bag = (new CreateVerticesAndupdate())->getCommandBag();
            $expected = $this->getExpectedCommand('create-and-update');

            $actual = $this->processor()->process($bag);
            $this->assertEquals($expected, $actual, 'failed to return expected Command for simple select bag');
        });

        $this->specify("it creates (C) vertices and updates (U) using an embedded retrieve (R)", function () {
            $bag = (new CreateVerticesAndUpdateEmbedded())->getCommandBag();
            $expected = $this->getExpectedCommand('create-and-update');

            $actual = $this->processor()->process($bag);
            $this->assertEquals($expected, $actual, 'failed to return expected Command for simple select bag');
        });
    }

//    public function testScenarios()
//    {
//        $this->specify(
//            "it find (R) existing vertices and creates (C) an edge between them then updates (U) that edge", function () {
//            $bag = (new RetrieveExistingVerticesCreateEdgeUpdateEdge())->getCommandBag();
//            $expected = $this->getExpectedCommand('find-vertices-create-edge-update');
//
//            $actual = $this->processor()->process($bag);
//            $this->assertEquals($expected, $actual, 'failed to return expected Command for simple select bag');
//        });
//    }


    /* Internals */
    public function getExpectedCommand($alias)
    {
        $method = $this->camelCase($alias);
        return $this->$method();
    }

    protected function camelCase($alias)
    {
        $key = str_replace("-", " ", $alias);
        $key = ucwords($key);
        $key = str_replace(" ", "", $key);
        $key = lcfirst($key);
        return $key;
    }

    protected function getData()
    {
        return ['one' => 1, 'two' => 'two', 'three' => false];
    }

    protected function getWheres()
    {
        return [
            ['one', Bag::COMPARATOR_EQUAL, 'one', Bag::CONJUNCTION_AND],
            ['two', Bag::COMPARATOR_GT, 2, Bag::CONJUNCTION_AND],
            ['three', Bag::COMPARATOR_LT, 3.14, Bag::CONJUNCTION_OR],
            ['four', Bag::COMPARATOR_EQUAL, true, Bag::CONJUNCTION_AND]
        ];
    }

    /* Methods to Implement */
    /** Returns a valid CommandProcessor */
    abstract public function processor();

    /**
     * Returns a command for the the Bag tested in
     * testInsert:it processes a simple insert bag
     */
    abstract public function insertSimple();

    /**
     * Returns a command for the the Bag tested in
     * testUpdate:it processes a simple update bag
     */
//    abstract public function updateSimple();

    /**
     * Returns a command for the the Bag tested in
     * testUpdate:it processes a complex update bag
     */
//    abstract public function updateComplex();

    /**
     * Returns a command for the the Bag tested in
     * testDelete:it processes a simple delete bag
     */
//    abstract public function deleteSimple();

    /**
     * Returns a command for the the Bag tested in
     * testDelete:it processes a complex delete bag
     */
//    abstract public function deleteComplex();

    /**
     * Returns a command for the the Bag tested in
     * testSelect:it processes a simple select bag
     */
    abstract public function selectSimple();

    /**
     * Returns a command for the the Bag tested in
     * testSelect:it processes a select bag with here constraints
     */
    abstract public function selectConstraints();

    /**
     * Returns a command for the the Bag tested in
     * testSelect:it processes a complex group by select bag
     */
    abstract public function selectGroupBy();

    /**
     * Returns a command for the the Bag tested in
     * testSelect:it processes a complex order byselect bag
     */
    abstract public function selectOrderBy();

    abstract public function getNativeId();
}
