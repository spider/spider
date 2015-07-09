<?php
namespace Michaels\Spider\Queries;

use InvalidArgumentException;
use Michaels\Manager\Contracts\ManagesItemsInterface;
use Michaels\Spider\Connections\ConnectionInterface;

/**
 * Class QueryBuilder
 * @package Michaels\Spider\Queries
 */
class Builder
{
    protected $connection;
    protected $processor;
    protected $bag;

    protected $currentScript;

    public function __construct(
        QueryProcessorInterface $processor,
        ConnectionInterface $connection = null,
        ManagesItemsInterface $bag = null
    )
    {
        $this->processor = $processor;
        $this->connection = $connection;
        $this->bag = $bag ?: new Bag();
    }

    public function select($projections = null)
    {
        $this->bag->command = 'select';
        $this->setProjections($projections);
        return $this;
    }

    public function only($projections)
    {
        $this->setProjections($projections);
        return $this;
    }

    public function record($id)
    {
        return $this->from($id);
    }

    public function byId($id)
    {
        return $this->record($id);
    }

    public function from($from)
    {
        $this->bag->from = $from;
        return $this;
    }

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
            $operator,
            $this->castValue($value),
            $conjunction
        ];

        return $this;
    }

    public function orWhere($property, $value = null, $operator = '=')
    {
        return $this->where($property, $value, $operator, 'OR');
    }

    public function andWhere($property, $value = null, $operator = '=')
    {
        return $this->where($property, $value, $operator, 'AND');
    }

    public function limit($limit)
    {
        $this->bag->limit = $limit;
        return $this;
    }

    public function groupBy($fields)
    {
        $fields = $this->fromCsv($fields);
        $this->bag->groupBy = $fields;
        return $this;
    }

    public function orderBy($fields)
    {
        $fields = $this->fromCsv($fields);
        $this->bag->orderBy = $fields;
        return $this;
    }

    public function asc()
    {
        $this->bag->orderAsc = true;
        return $this;
    }

    public function desc()
    {
        $this->bag->orderAsc = false;
        return $this;
    }


    public function clear($properties = null)
    {
        $this->bag = new Bag($properties);
    }

    public function query($script)
    {
        $this->connection->open();
        $results = $this->connection->executeReadCommand(new Command($script));
        $this->connection->close();

        return $results;
    }

    public function getScript()
    {
        $this->currentScript = $this->processor->process($this->bag);
        return $this->currentScript;
    }

    protected function setProjections($projections)
    {
        if (is_null($projections)) {
            $this->bag->projections = [];
            return $this;
        }

        $this->bag->projections = $this->fromCsv($projections, true);
        return $this;
    }

    protected function fromCsv($fields, $throwException = true)
    {
        if (is_array($fields)) {
            return $fields;

        } elseif (is_string($fields)) {
            return array_map('trim', explode(",", $fields));
        }

        // We can't do anything with this value
        if ($throwException) {
            throw new InvalidArgumentException("Projections must be a comma-separated string or an array");
        }

        return $fields;
    }

    /**
     * @param $value
     * @return string
     */
    protected function castValue($value)
    {
        if ($value === true) {
            $value = 'true';

        } elseif ($value === false) {
            $value = 'false';

        } elseif (is_string($value)) {
            $value = "'$value'";
        }
        return (string)$value;
    }
}
