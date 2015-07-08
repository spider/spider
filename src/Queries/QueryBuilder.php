<?php
namespace Michaels\Spider\Queries;

use Michaels\Spider\Connections\ConnectionInterface;

/**
 * Class QueryBuilder
 * @package Michaels\Spider\Queries
 */
class QueryBuilder
{
    protected $connection;
    protected $command;
    protected $projections;
    protected $currentScript;
    protected $from;

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

        } elseif (is_array($projections)) {
            $this->projections = $projections;
            return $this;

        } elseif (is_string($projections)) {
            $this->projections = array_map('trim', explode(",", $projections));
            return $this;
        }

        throw new \InvalidArgumentException("Projections must be a comma-separated string or an array");
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

    public function getScript()
    {
        $this->currentScript = $this->process();
        return $this->currentScript;
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

        return $script;
    }
}
