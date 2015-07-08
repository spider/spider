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

        return $script;
    }
}
