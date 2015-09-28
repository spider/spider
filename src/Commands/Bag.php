<?php
namespace Spider\Commands;

use Spider\Base\Object;

/**
 * Command Bag
 *
 * Holds the parameters for a command to be processed by a
 * specific Language Processor
 */
class Bag extends Object
{
    /* Required Bag Contents */
    /**
     * Data for edges and vertices to be created
     *
     * [
     *     TYPE => EDGE|VERTEX,
     *     LABEL => 'friend',
     *
     *      // For Edges
     *     INV => VertexID or `BaseBuilder` instance,
     *     OUTV =>  VertexID or `BaseBuilder` instance,
     *     'other' => 'properties',
     *     'here' => true
     * ]
     *
     * @var null|array
     */
    public $create = null;

    /**
     * @var array list of projections (fields affected)
     * Null default to all fields (*)
     *
     * @var null|array
     */
    public $retrieve = null;

    /**
     * Data for edges and vertices to be updated
     * Will merge this array of data with the vertex/edge data
     *
     * @var null|array
     */
    public $update = null;

    /**
     * Whether or not to delete
     * @var null|bool
     */
    public $delete = null;

    /* Optional Bag Contents with defaults */
    /**
     * @var array list of constraints
     *
     * `[projection, operator, value, conjunction]`
     * `['username', static::COMPARATOR_EQUAL, 'michael', static::CONJUNCTION_AND]`
     * `AND WHERE username = 'michael'` for example
     */
    public $where = [];

    /** @var bool|int How many results to return. `false` no limit */
    public $limit = null;

    /** @var bool|array Which field to group results by. `false` no grouping */
    public $groupBy = null;

    /** @var bool|array Which field to order results by. `false` no ordering */
    public $orderBy = null;

    /**
     * Flag a mapping format for the query to return
     * `Builder` allows SET, PATH, and TREE.
     * Defaults to SET, which means a normal response
     *
     * @var int Constant MAP_*
     */
    public $map = 300; // defaults to MAP_SET

    /* Constants */
    /* ToDo: Is it best to move the constants to their own class? */

    /* Comparators for constraints */
    const COMPARATOR_EQUAL   = 10; // =
    const COMPARATOR_LT      = 20; // <
    const COMPARATOR_GT      = 30; // >
    const COMPARATOR_GE      = 40; // >=
    const COMPARATOR_LE      = 50; // <=
    const COMPARATOR_NE      = 60; // not equal
    const COMPARATOR_WITHOUT = 70;
    const COMPARATOR_IN      = 80;

    /* Conjunctions for constraints */
    const CONJUNCTION_AND = 100;
    const CONJUNCTION_OR  = 110;
    const CONJUNCTION_XOR = 120;
    const CONJUNCTION_NOT = 130;

    /* Maps */
    const MAP_SET  = 300;
    const MAP_PATH = 310;
    const MAP_TREE = 320;

    /* Orders */
    const ORDER_ASC  = 400;
    const ORDER_DESC = 410;

    /* Elements */
    const ELEMENT_VERTEX = 500;
    const ELEMENT_EDGE   = 510;
    const ELEMENT_LABEL  = 520;
    const ELEMENT_ID     = 530;
    const ELEMENT_TYPE = 540;
    const EDGE_INV = 550;
    const EDGE_OUTV = 560;

    public function prepare()
    {
        /*
         * Should validate that
         *     1. Any edge creation includes INV and OUTV
         *     2. Every 'where' clause defaults to ELEMENT_TYPE = ELEMENT_VERTEX
         */
    }
}
