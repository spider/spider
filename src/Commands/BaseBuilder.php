<?php
namespace Spider\Commands;

use InvalidArgumentException;

/**
 * Fluent Command Builder, simple bag manipulation
 * No awareness of connection OR processor
 */
class BaseBuilder
{
    /** @var Bag The CommandBag with command parameters */
    protected $bag;

    /**
     * A map of operators and conjunctions
     * These signs on the left are can be used in `where` constraints and such
     * @var array
     */
    public $operators = [
        '=' => Bag::COMPARATOR_EQUAL,
        '>' => Bag::COMPARATOR_GT,
        '<' => Bag::COMPARATOR_LT,
        '<=' => Bag::COMPARATOR_LE,
        '>=' => Bag::COMPARATOR_GE,
        '<>' => Bag::COMPARATOR_NE,

        'AND' => Bag::CONJUNCTION_AND,
        'OR' => Bag::CONJUNCTION_OR
    ];

    /**
     * Creates a new instance of the Command Builder
     * @param Bag|null $bag
     */
    public function __construct(Bag $bag = null)
    {
        $this->bag = $bag ?: new Bag();
    }

    /* Fluent Methods for building queries */
    /**
     * Add an `insert` clause to the current command bag
     * @param array $data
     * @return mixed
     */
    public function create(array $data)
    {
        $this->bag->command = Bag::COMMAND_CREATE;

        if (isset($data[0]) && is_array($data[0])) {
            $this->bag->createCount = count($data);
        } else {
            $this->bag->createCount = 1;
        }

        $this->bag->data = $data;

        return $this;
    }

    /**
     * Add a `retrieve` clause to the current Command Bag
     *
     * @param null $projections Specific fields to retrieve (defaults to *)
     * @return $this
     */
    public function retrieve($projections = null)
    {
        $this->bag->command = Bag::COMMAND_RETRIEVE;
        $this->projections($projections);
        return $this;
    }

    /**
     * An an `update` clause to the current command bag
     * @param null $property
     * @param null $value
     * @return $this
     */
    public function update($property = null, $value = null)
    {
        $this->bag->command = Bag::COMMAND_UPDATE;

        // We're just setting the command
        if (is_null($property)) {
            return $this;
        }

        // Or, We're adding a single bit of data as well
        if (!is_null($value)) {
            $this->data($property, $value);
            return $this;
        }

        // Okay, so we only have a $property. That leaves us with 2 possibilities
        // First, the $property is an array of data to be added
        if (is_array($property)) {
            $this->data($property);
            return $this;

            // Second, the $property is a target
        } else {
            $this->target($property);
            return $this;
        }
    }

    /**
     * Add a `delete` clause to the current command bag
     * @return $this|mixed
     */
    public function delete()
    {
        $this->bag->command = Bag::COMMAND_DELETE;
        return $this;
    }

    /**
     * Add data to the current command bag (for insert and update)
     * @param $property
     * @param null $value
     * @return $this
     */
    public function data($property, $value = null)
    {
        if (is_array($property)) {
            foreach ($property as $key => $value) {
                $this->data($key, $value);
            }
        } else {
            $this->bag->data[$property] = $value;
        }
        return $this;
    }

    /**
     * Set the target in the current Command Bag
     * @param $target
     * @return $this
     */
    public function target($target)
    {
        $this->bag->target = $target;
        return $this;
    }

    /**
     * Set the projection fields in the current Command Bag
     *
     * This is used by `only()`, `select()`, and others. A projection is
     * a field affected by the current command. Like `SELECT fieldname` in SQL
     *
     * @param $projections
     * @return $this
     */
    public function projections($projections)
    {
        if (is_null($projections)) {
            $this->bag->projections = [];
            return $this;
        }

        // Ensure $projects is usable
        if (!is_string($projections) && !is_array($projections)) {
            throw new InvalidArgumentException("Projections must be a comma-separated string or an array");
        }

        $this->bag->projections = $this->csvToArray($projections);
        return $this;
    }

    /**
     * Add a single or multiple `where` constraint to the current Command Bag
     *
     * @param string $property Field name
     * @param mixed $value Value matched against
     * @param string $operator From the `self::$operators` array
     * @param string $conjunction From the `self::$operators` array
     * @return $this
     */
    public function where($property, $value = null, $operator = '=', $conjunction = 'AND')
    {
        if (is_array($property)) {
            if (is_array($property[0])) { // We were handed an array of constraints
                foreach ($property as $constraint) {
                    $this->where(
                        $constraint[0], // property
                        $constraint[2] ?: $operator, // operator, default =
                        $constraint[1], // value
                        isset($constraint[3]) ? $constraint[3] : $conjunction // conjunction, default AND
                    );
                }
                return $this;
            }

            $this->where(
                $property[0], // property
                $property[2] ?: $operator, // operator, default =
                $property[1], // value
                isset($property[3]) ? $property[3] : $conjunction // conjunction, default AND
            );
            return $this;
        }

        $this->bag->where[] = [
            $property,
            $this->signToConstant($operator), // convert to constant
            $value,
            $this->signToConstant($conjunction) // convert to constant
        ];

        return $this;
    }

    /**
     * Set the result limit in the current Command Bag
     * @param $limit
     * @return $this
     */
    public function limit($limit)
    {
        $this->bag->limit = $limit;
        return $this;
    }

    /**
     * Set which field to group results by in the current Command Bag
     * @param $fields
     * @return $this
     */
    public function groupBy($fields)
    {
        $fields = $this->csvToArray($fields);
        $this->bag->groupBy = $fields;
        return $this;
    }

    /**
     * Set which fields to order results by in the current Command Bag
     * @param $fields
     * @return $this
     */
    public function orderBy($fields)
    {
        $fields = $this->csvToArray($fields);
        $this->bag->orderBy = $fields;
        return $this;
    }

    /**
     * Return results in ascending order
     * @return $this
     */
    public function asc()
    {
        $this->bag->orderAsc = true;
        return $this;
    }

    /**
     * Return results in descending order
     * @return $this
     */
    public function desc()
    {
        $this->bag->orderAsc = false;
        return $this;
    }

    /* Manage the Builder itself */
    /**
     * Clear the current Command Bag
     * @param array $properties
     */
    public function clear($properties = [])
    {
        $this->bag = new Bag($properties);
    }

    /**
     * Return the current Command Bag
     * @return Bag
     */
    public function getCommandBag()
    {
        return $this->bag;
    }

    /* Internals */
    /**
     * Turns a Comma Separated Sting into an array. Used to set projections.
     *
     * If $throwException is not null|false, an exception will be thrown with
     * the string value of $throwException
     *
     * @param $string
     * @return array
     */
    protected function csvToArray($string)
    {
        if (is_string($string)) {
            return array_map('trim', explode(",", $string));
        }

        return $string;
    }

    /**
     * Turns a user-inputted sign into a constant
     *
     * Used to turn things like '=' into Bag::COMPARATOR_EQUAL
     * in where constraints
     *
     * @param $sign
     * @return mixed
     */
    protected function signToConstant($sign)
    {
        return $this->operators[$sign];
    }
}
