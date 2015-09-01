<?php
namespace Spider\Test\Unit\Commands\Languages;

use Codeception\Specify;
use Spider\Commands\Bag;

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
    public function testInsert()
    {
        $this->specify("it processes a simple insert V bag", function () {
            $bag = new Bag();
            $bag->command = Bag::COMMAND_CREATE;
            $bag->target = Bag::ELEMENT_VERTEX;
            $bag->data = [$this->getData() + [Bag::ELEMENT_LABEL => 'target']];

            $expected = $this->getExpectedCommand('insert-simple');

            $actual = $this->processor()->process($bag);
            $this->assertEquals($expected, $actual, 'failed to return expected Command for simple select bag');
        });

        $this->specify("it processes a multiple insert V bag", function () {
            $bag = new Bag();
            $bag->command = Bag::COMMAND_CREATE;
            $bag->target = Bag::ELEMENT_VERTEX;

            /* ToDo: data is too rigid. See note in BaseTest */
            $bag->data = [
                [
                    'name' => 'mal',
                    'role' => 'captain',
                    'ship' => 'firefly',
                    Bag::ELEMENT_LABEL => 'target',
                ],
                [
                    'name' => 'zoe',
                    'role' => 'first',
                    'husband' => 'wash',
                    Bag::ELEMENT_LABEL => 'target',
                ],
                [
                    'name' => 'book',
                    'role' => 'shepherd',
                    'past' => 'unknown',
                    Bag::ELEMENT_LABEL => 'target',
                ]
            ];

            $expected = $this->getExpectedCommand('insert-multiple');

            $actual = $this->processor()->process($bag);
            $this->assertEquals($expected, $actual, 'failed to return expected Command for multiple insert');
        });
    }


    public function testUpdate()
    {
        $this->specify("it processes a simple update bag", function () {

            $bag = new Bag();
            $bag->command = Bag::COMMAND_UPDATE;
            $bag->target = Bag::ELEMENT_VERTEX;
            $bag->data = [$this->getData()];
            $bag->where = [[
                Bag::ELEMENT_ID,
                Bag::COMPARATOR_EQUAL, // convert to constant
                'target_id',
                Bag::CONJUNCTION_AND // convert to constant
            ]];

            $expected = $this->getExpectedCommand('update-simple');

            $actual = $this->processor()->process($bag);
            $this->assertEquals($expected, $actual, 'failed to return expected Command for simple select bag');
        });

        $this->specify("it processes a complex update bag", function () {

            $bag = new Bag();
            $bag->command = Bag::COMMAND_UPDATE;
            $bag->target = Bag::ELEMENT_VERTEX; // don't forget about TargetID
            $bag->data = [$this->getData()];
            $bag->where = array_merge($this->getWheres(), [[
                Bag::ELEMENT_LABEL,
                Bag::COMPARATOR_EQUAL, // convert to constant
                'target',
                Bag::CONJUNCTION_AND // convert to constant
            ]]);
            $bag->limit = 10;

            $expected = $this->getExpectedCommand('update-complex');

            $actual = $this->processor()->process($bag);
            $this->assertEquals($expected, $actual, 'failed to return expected Command for simple select bag');
        });
    }

    public function testDelete()
    {
        $this->specify("it processes a simple delete bag", function () {

            $bag = new Bag();
            $bag->command = Bag::COMMAND_DELETE;
            $bag->target = Bag::ELEMENT_VERTEX;
            //$bag->data = $this->getData();
            $bag->where = [[
                Bag::ELEMENT_ID,
                Bag::COMPARATOR_EQUAL, // convert to constant
                'target_id',
                Bag::CONJUNCTION_AND // convert to constant
            ]];

            $expected = $this->getExpectedCommand('delete-simple');

            $actual = $this->processor()->process($bag);
            $this->assertEquals($expected, $actual, 'failed to return expected Command for simple select bag');
        });

        $this->specify("it processes a complex delete bag", function () {

            $bag = new Bag();
            $bag->command = Bag::COMMAND_DELETE;
            $bag->target = Bag::ELEMENT_VERTEX;
            $bag->where = array_merge($this->getWheres(), [[
                Bag::ELEMENT_LABEL,
                Bag::COMPARATOR_EQUAL, // convert to constant
                'target',
                Bag::CONJUNCTION_AND // convert to constant
            ]]);
            $bag->limit = 10;

            $expected = $this->getExpectedCommand('delete-complex');

            $actual = $this->processor()->process($bag);
            $this->assertEquals($expected, $actual, 'failed to return expected Command for simple select bag');
        });
    }

    public function testSelect()
    {
        $this->specify("it processes a simple select bag for vertices", function () {

            $bag = new Bag();
            $bag->command = Bag::COMMAND_RETRIEVE;
            $bag->target = Bag::ELEMENT_VERTEX;
            $bag->where = [[
                Bag::ELEMENT_LABEL,
                Bag::COMPARATOR_EQUAL, // convert to constant
                'target',
                Bag::CONJUNCTION_AND // convert to constant
            ]];

            $expected = $this->getExpectedCommand('select-simple');

            $actual = $this->processor()->process($bag);
            $this->assertEquals($expected, $actual, 'failed to return expected Command for simple select bag');
        });

        $this->specify("it processes a simple select bag for edges", function () {

            $bag = new Bag();
            $bag->command = Bag::COMMAND_RETRIEVE;
            $bag->target = Bag::ELEMENT_EDGE;
            $bag->where = [[
                Bag::ELEMENT_LABEL,
                Bag::COMPARATOR_EQUAL, // convert to constant
                'target',
                Bag::CONJUNCTION_AND // convert to constant
            ]];

            $expected = $this->getExpectedCommand('select-simple-edge');

            $actual = $this->processor()->process($bag);
            $this->assertEquals($expected, $actual, 'failed to return expected Command for simple select bag');
        });

        // @todo do the same as above for both (requires traversals for neo

        $this->specify("it processes where constraints in select", function () {

            $bag = new Bag();
            $bag->command = Bag::COMMAND_RETRIEVE;
            $bag->target = Bag::ELEMENT_VERTEX;
            $bag->where = array_merge($this->getWheres(), [[
                Bag::ELEMENT_LABEL,
                Bag::COMPARATOR_EQUAL, // convert to constant
                'target',
                Bag::CONJUNCTION_AND // convert to constant
            ]]);

            $expected = $this->getExpectedCommand('select-constraints');

            $actual = $this->processor()->process($bag);
            $this->assertEquals($expected, $actual, 'failed to return expected Command for simple select bag');
        });

        $this->specify("it processes a complex order by select bag", function () {

            $bag = new Bag();
            $bag->command = Bag::COMMAND_RETRIEVE;
            $bag->projections = ['field1', 'field2'];
            $bag->target = Bag::ELEMENT_VERTEX;
            $bag->limit = 3;
            $bag->orderBy = [['field1', Bag::ORDER_DESC]];
            $bag->where = array_merge($this->getWheres(), [[
                Bag::ELEMENT_LABEL,
                Bag::COMPARATOR_EQUAL, // convert to constant
                'target',
                Bag::CONJUNCTION_AND // convert to constant
            ]]);

            $expected = $this->getExpectedCommand('select-order-by');

            $actual = $this->processor()->process($bag);
            $this->assertEquals($expected, $actual, 'failed to return expected Command');
        });
    }

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
    /** Returns a valid CommandProcesor */
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
    abstract public function updateSimple();

    /**
     * Returns a command for the the Bag tested in
     * testUpdate:it processes a complex update bag
     */
    abstract public function updateComplex();

    /**
     * Returns a command for the the Bag tested in
     * testDelete:it processes a simple delete bag
     */
    abstract public function deleteSimple();

    /**
     * Returns a command for the the Bag tested in
     * testDelete:it processes a complex delete bag
     */
    abstract public function deleteComplex();

    /**
     * Returns a command for the the Bag tested in
     * testSelect:it processes a simple select bag
     */
    abstract public function selectSimple();

    /**
     * Returns a command for the the Bag tested in
     * testSelect:it processes a select bag with here constraints
     */
    abstract  public function selectConstraints();

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
}
