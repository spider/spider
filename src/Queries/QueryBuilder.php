<?php
namespace Michaels\Spider\Queries;

use InvalidArgumentException;
use Michaels\Spider\Connections\ConnectionInterface;

/**
 * Class QueryBuilder
 * @package Michaels\Spider\Queries
 */
class QueryBuilder
{
    protected $connection;
    protected $currentScript;

    protected $command;
    protected $projections;
    protected $from;
    protected $limit;
    protected $groupBy;
    protected $orderBy;
    protected $orderAsc = true;

    public function __construct(ConnectionInterface $connection = null)
    {
        $this->connection = $connection;
    }

    public function reset()
    {
        return new static($this->connection);
    }

    public function query($script)
    {
        $this->connection->open();
        $results = $this->connection->executeReadCommand(new Query($script));
        $this->connection->close();

        return $results;
    }

    public function select($projections = null)
    {
        $this->command = "SELECT";
        $this->setProjections($projections);
        return $this;
    }

    public function setProjections($projections)
    {
        if (is_null($projections)) {
            $this->projections = [];
            return $this;
        }

        $this->projections = $this->fromCsv($projections, true);
        return $this;
    }

    public function fromCsv($fields, $throwException = true)
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

    public function only($projections)
    {
        $this->setProjections($projections);
        return $this;
    }

    public $where = [];

    public function orWhere($property, $value = null, $operator = '=')
    {
        return $this->where($property, $value, $operator, 'OR');
    }

    public function andWhere($property, $value = null, $operator = '=')
    {
        return $this->where($property, $value, $operator, 'AND');
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

        $this->where[] = [
            $property,
            $operator,
            $this->castValue($value),
            $conjunction
        ];

        return $this;
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
        $this->from = $from;
        return $this;
    }

    public function limit($limit)
    {
        $this->limit = $limit;
        return $this;
    }

    public function groupBy($fields)
    {
        $fields = $this->fromCsv($fields);

//        if (count($fields) > 1) {
//            throw new InvalidArgumentException("Orient DB only allows one field in Group By");
//        }

        $this->groupBy = $fields;

        return $this;
    }

    public function orderBy($fields)
    {
        $fields = $this->fromCsv($fields);

//        if (count($fields) > 1) {
//            throw new InvalidArgumentException("Orient DB only allows one field in Group By");
//        }

        $this->orderBy = $fields;

        return $this;
    }

    public function asc()
    {
        $this->orderAsc = true;
        return $this;
    }

    public function desc()
    {
        $this->orderAsc = false;
        return $this;
    }

    protected function process()
    {
        // NOTE: the whitespace should be placed by the new clause at beginning, not the previous clause at the end

        // COMMAND
        $script = $this->command;

        // <projections>
        if (!empty($this->projections)) {
            $script .= " " . trim(implode(", ", $this->projections), ", ");
        }

        // FROM
        $script .= " FROM " . $this->from;

        // WHERE
        if (!empty($this->where)) {
            $script .= " WHERE";

            foreach ($this->where as $index => $value) {
                if ($index !== 0) { // dont add conjunction to the first clause
                    $script .= " $value[3]";
                }

                $script .= " $value[0] $value[1] $value[2]";
            }
        }

        // GROUP BY
        if (is_array($this->groupBy)) {
            $script .= " GROUP BY";

            foreach ($this->groupBy as $index => $field) {
                if ($index !== 0) {
                    $script .= ",";
                }

                $script .= " $field";
            }
        }

        // ORDER BY
        if (is_array($this->orderBy)) {
            $script .= " ORDER BY";

            foreach ($this->orderBy as $index => $field) {
                if ($index !== 0) {
                    $script .= ",";
                }

                $script .= " $field";
            }

            $script .= ($this->orderAsc) ? ' ASC' : ' DESC';
        }

        // LIMIT
        if ($this->limit) {
            $script .= " LIMIT " . (string)$this->limit;
        }

        return $script;
    }

    public function getScript()
    {
        $this->currentScript = $this->process();
        return $this->currentScript;
    }
}
