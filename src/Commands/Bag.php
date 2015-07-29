<?php
namespace Spider\Commands;

use Spider\Base\Object;

/**
 * Command Bag
 *
 * Holds the parameters for a command to be processed by a
 * Driver specific Command Processor
 */
class Bag extends Object
{
    /* Required Bag Contents */
    /** @var string Create, Retrieve, Update, Delete */
    public $command = null;

    /**
     * Target of the command.
     * Either a string for a label or instance of Commands\TargetID for a specific record
     *
     * ToDo: Make TargetID a generic ID (and maybe Label) class
     *
     * @var string|TargetID
     */
    public $target = null;

    /* Optional Bag Contents with defaults */
    /**
     * @var array list of projections (fields affected)
     * Empty array default to all fields (*)
     */
    public $projections = [];

    /**
     * @var array list of constraints
     *
     * `[projection, operator, value, conjunction]`
     * `['username', static::COMPARATOR_EQUAL, 'michael', 'AND']`
     */
    public $where = [];

    /** @var array Data to be inserted/updated */
    public $data = [];

    /** @var int How many records to create */
    public $createCount = 0;

    /** @var mixed What Response Format to return after CUD command. Defaults to nothing */
    public $return = false;

    /** @var bool|int How many results to return. `false` no limit */
    public $limit = false;

    /** @var bool|string Which field to group results by. `false` no grouping */
    public $groupBy = false;

    /** @var bool|string Which field to order results by. `false` no ordering */
    public $orderBy = false;

    /** @var bool Order results Ascending (true) or Descending (false) */
    public $orderAsc = true;

    /* Constants */
    /* ToDo: Is it best to move the constants to their own class? */

    /* Comparators for constraints */
    const COMPARATOR_EQUAL = 10; // =
    const COMPARATOR_LT = 20; // <
    const COMPARATOR_GT = 30; // >
    const COMPARATOR_GE = 40; // >=
    const COMPARATOR_LE = 50; // <=
    const COMPARATOR_NE = 60; // not equal
    const COMPARATOR_WITHOUT = 70;

    /* Conjunctions for constraints */
    const CONJUNCTION_AND = 100;
    const CONJUNCTION_OR = 110;

    /* CRUD commands (equivalent to SELECT, UPDATE, INSERT, DROP) */
    const COMMAND_CREATE = 200;
    const COMMAND_RETRIEVE = 210;
    const COMMAND_UPDATE = 220;
    const COMMAND_DELETE = 230;

    /**
     * Return a new instance of a Command Bag
     * @param array|null $properties
     * @return static
     */
    public static function make(array $properties = null)
    {
        return new static($properties);
    }
}
