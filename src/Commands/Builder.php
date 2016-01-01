<?php
namespace Spider\Commands;

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
        return $this->internalRetrieve($projections);
    }

    /**
     * Add a `insert` clause to the current Command Bag
     *
     * Alias of retrieve
     *
     * @param array|null $data
     * @return Builder
     */
    /* ToDo: Figure out API Builder sugar for inserting records */
    public function insert(array $data = null)
    {
        return $this->internalCreate($data);
    }

    /**
     * Delete a single record
     * @param null $record
     * @return Builder
     */
    public function delete($record = null)
    {
        $this->internalDelete(); // set the delete command

        if (is_array($record)) {
            return $this->records($record);
        }

        if (!is_null($record)) {
            return $this->record($record);
        }

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
        // We're adding a single bit of data as well
        if (!is_null($value)) {
            return $this->internalUpdate([$property => $value]);
        }

        return $this->internalUpdate($property);
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
     * @throws \Exception
     */
    public function type($type)
    {
        if (is_string($type)) {
            switch (strtolower($type)) {
                case 'edge':
                    $type = Bag::ELEMENT_EDGE;
                    break;

                case 'vertex':
                    $type = Bag::ELEMENT_VERTEX;
                    break;

                default:
                    throw new \Exception("$type is not a valid element type");
            }
        }

        return $this->where(Bag::ELEMENT_TYPE, $type);
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
        $this->limit(1);
        return $this;
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
