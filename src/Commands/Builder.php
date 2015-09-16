<?php
namespace Spider\Commands;

use Spider\Commands\Languages\ProcessorInterface;

/**
 * Command Builder with sugar, no awareness of connections
 * Optional CommandProcessor
 */
class Builder extends BaseBuilder
{
    /**
     * A map of operators and conjunctions
     * These signs on the left are can be used in `where` constraints and such
     * @var array
     */
    public $operators = [
        '='  => Bag::COMPARATOR_EQUAL,
        '>'  => Bag::COMPARATOR_GT,
        '<'  => Bag::COMPARATOR_LT,
        '<=' => Bag::COMPARATOR_LE,
        '>=' => Bag::COMPARATOR_GE,
        '<>' => Bag::COMPARATOR_NE,
        'IN' => Bag::COMPARATOR_IN,

        'AND' => Bag::CONJUNCTION_AND,
        'OR'  => Bag::CONJUNCTION_OR,
        'XOR' => Bag::CONJUNCTION_XOR,
        'NOT' => Bag::CONJUNCTION_NOT,
    ];

    /**
     * Creates a new instance of the Command Builder
     * With an optional language processor
     *
     * @param ProcessorInterface|null $processor
     * @param Bag|null $bag
     */
    public function __construct(
        ProcessorInterface $processor = null,
        Bag $bag = null
    ) {
        parent::__construct($bag);
    }

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
     * @param array|null $data
     * @return Builder
     */
    public function insert(array $data = null)
    {
        //case of single entry, and case of multiple entries
        if (!is_array($data) || !isset($data[0]) || !is_array($data[0])) {
            //single entry situation.
            $data = [$data];
        }
        return parent::insert($data);
    }

    /**
     * Delete a single record
     * @param null $record
     * @return Builder
     */
    public function drop($record = null)
    {
        $this->delete(); // set the delete command

        if (is_array($record)) {
            return $this->records($record);
        }

        if (!is_null($record)) {
            return $this->record($record);
        }

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
        /* We were a full constraint array(s) */
        if (is_array($property)) {

            // We were handed multiple constraints
            if (is_array($property[0])) {
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

            // We were handed a single, full constraint
            $this->where(
                $property[0], // property
                $property[2] ?: $operator, // operator, default =
                $property[1], // value
                isset($property[3]) ? $property[3] : $conjunction // conjunction, default AND
            );
            return $this;
        }

        /* Were we handed parameters for arrays? */
        $this->internalWhere([
            $property,
            $this->signToConstant($operator), // convert to constant
            $value,
            $this->signToConstant($conjunction) // convert to constant
        ]);

        return $this;
    }

    /**
     * Set the type of the target in the current Command Bag
     * @param $type
     * @return $this
     */
    public function type($type)
    {
        $this->addToBag('target', $type);
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
        if (!is_array($property)) {
            return $this->data([$property => $value]);
        } else {
            $newData = $this->getFromCurrentBag('data');
            $newData[] = $property;
            $this->addToBag('data', $newData);
            return $this;
        }
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
        $this->projections($projections);
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
            return $this->where(Bag::ELEMENT_ID, $id, 'IN');
        }
        return $this->where(Bag::ELEMENT_ID, $id, '=');
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
     * Set the target label in the current Command Bag
     * Alias for label
     *
     * @param $label
     * @return $this
     */
    public function from($label)
    {
        return $this->label($label);
    }

    /**
     * Set the target label in the current Command Bag
     * Alias for label
     *
     * @param $label
     * @return $this
     */
    public function label($label)
    {
        return $this->where(Bag::ELEMENT_LABEL, $label, '=');
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
    public function andWhere($property, $value, $operator = '=')
    {
        return $this->where($property, $value, $operator, 'AND');
    }

    /* Set limits */
    /**
     * Retrieve one result by dispatching the current Command Bag.
     * Alias of `one()`
     * @return Builder
     */
    public function one()
    {
        $this->bag->limit = 1;
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
        //The is one situation in which we will want to reformat

        // Or, We're adding a single bit of data as well
        if (!is_null($value)) {
            return parent::update([$property => $value]);
        }

        return parent::update($property);
    }

    /**
     * Turns a user-inputted sign into a constant
     *
     * Used to turn things like '=' into Bag::COMPARATOR_EQUAL
     * in where constraints
     *
     * @param string $sign
     * @return mixed
     */
    protected function signToConstant($sign)
    {
        if (is_int($sign)) {
            return $sign;
        }

        return $this->operators[$sign];
    }
}
