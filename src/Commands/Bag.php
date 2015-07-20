<?php
namespace Michaels\Spider\Commands;

/**
 * Command Bag
 *
 * Holds the parameters for a command to be processed by a
 * Driver specific Command Processor
 */
class Bag
{
    /* Required Bag Contents */
    /** @var string Create, Retrieve, Update, Delete */
    public $command = null;

    /** @var string Target */
    public $from = null;

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
    const COMPARATOR_EQUAL = 'EQUAL'; // =
    const COMPARATOR_LT = 'LT'; // <
    const COMPARATOR_GT = 'GT'; // >
    const COMPARATOR_GE = 'GE'; // >=
    const COMPARATOR_LE = 'LE'; // <=
    const COMPARATOR_NE = 'NE'; // not equal
    const COMPARATOR_WITHOUT = 'WITHOUT';

    /* Conjunctions for constraints */
    const CONJUNCTION_AND = 'AND';
    const CONJUNCTION_OR = 'OR';

    /* CRUD commands (equivalent to SELECT, UPDATE, INSERT, DROP) */
    const COMMAND_CREATE = 'CREATE';
    const COMMAND_RETRIEVE = 'RETRIEVE';
    const COMMAND_UPDATE = 'UPDATE';
    const COMMAND_DELETE = 'DELETE';

    /**
     * Create a new Command Bag
     * @param array|null $properties
     */
    public function __construct(array $properties = null)
    {
        if ($properties) {
            foreach ($properties as $key => $value) {
                $this->$key = $value;
            }
        }
    }

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
