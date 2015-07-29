<?php
namespace Spider\Commands;

use InvalidArgumentException;
use Spider\Connections\ConnectionInterface;
use Spider\Graphs\ID as TargetID;

/**
 * Command Builder with sugar, no awareness of connections
 */
class Builder extends BaseBuilder
{
    /* Fluent Methods for building queries */
    /**
     * Add a `select` clause to the current Command Bag
     *
     * Alias of retrieve
     *
     * @param null $projections
     * @return Builder
     */
    public function select($projections = null)
    {
        return $this->retrieve($projections);
    }

    /**
     * Add a `select` clause to the current Command Bag
     *
     * Alias of retrieve
     *
     * @param null $projections
     * @return Builder
     */
    public function insert($projections = null)
    {
        return $this->create($projections);
    }

    /**
     * Update only the first record
     * @param $target
     * @return $this
     */
    public function updateFirst($target)
    {
        $this->bag->command = Bag::COMMAND_UPDATE;
        $this->limit(1);
        $this->target($target);

        return $this;
    }

    /**
     * Delete a single record
     * @param null $record
     * @return $this|mixed
     */
    public function drop($record = null)
    {
        $this->delete(); // set the delete command

        if (!is_null($record)) {
            $this->record($record);
        }

        return $this;
    }

    /**
     * Alias of `data()`
     * @param $property
     * @param null $value
     * @return Builder
     */
    public function withData($property, $value = null)
    {
        return $this->data($property, $value);
    }

    /**
     * Add specific projections to the current Command Bag
     * @param $projections
     * @return Builder
     */
    public function only($projections)
    {
        $this->setProjections($projections);
        return $this;
    }

    /**
     * Add a record id to the current Command Bag as target
     * @param string|int $id The id of the record
     * @return Builder
     */
    public function record($id)
    {
        if (is_array($id)) {
            $ids = array_map(function ($value) {
                return new TargetID($value);
            }, $id);

            return $this->from($ids);
        }

        return $this->from(new TargetID($id));
    }

    /**
     * Add several records as target
     * @param $ids
     * @return Builder
     */
    public function records($ids)
    {
        return $this->record($ids);
    }

    /**
     * Alias of record
     * Alias of `record()`
     *
     * @param string|int $id The id of the record
     * @return Builder
     */
    public function byId($id)
    {
        return $this->record($id);
    }

    /**
     * Set the target in the current Command Bag
     * @param $target
     * @return $this
     */
    public function from($target)
    {
        return $this->target($target);
    }

    /**
     * Alias of from, used for fluency
     * @param $target
     * @return Builder
     */
    public function into($target)
    {
        return $this->target($target);
    }

    /**
     * Add a `where` clause with an `OR` conjunction to the current Command Bag
     *
     * @param string $property Field name
     * @param mixed $value Value matched against
     * @param string $operator From the `self::$operators` array
     * @return $this
     */
    public function orWhere($property, $value = null, $operator = '=')
    {
        return $this->where($property, $value, $operator, 'OR');
    }

    /**
     * Add a `where` clause with an `AND` conjunction to the current Command Bag
     *
     * @param string $property Field name
     * @param mixed $value Value matched against
     * @param string $operator From the `self::$operators` array
     * @return $this
     */
    public function andWhere($property, $value = null, $operator = '=')
    {
        return $this->where($property, $value, $operator, 'AND');
    }

    /* Execute a command with limits */
    /**
     * Dispatch a retrieve command with no limit.
     * Return all the results
     * @return mixed Command results
     */
    public function all()
    {
        $this->bag->limit = false; // We want all records
    }

    /**
     * Retrieve the first result by dispatching the current Command Bag.
     * @return mixed Command results
     */
    public function one()
    {
        $this->bag->limit = 1;
    }

    /**
     * Retrieve the first result by dispatching the current Command Bag.
     * Alias of `one()`
     * @return mixed Command results
     */
    public function first()
    {
        return $this->one();
    }
}
