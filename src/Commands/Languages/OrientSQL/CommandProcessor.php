<?php
namespace Spider\Commands\Languages\OrientSQL;

use Spider\Commands\Bag;
use Spider\Commands\Command;
use Spider\Commands\CommandInterface;
use Spider\Commands\Languages\ProcessorInterface;
use Spider\Exceptions\NotSupportedException;
use Spider\Graphs\ID as TargetID;

/**
 * Class CommandProcessor
 * @package Spider\Drivers\OrientDB
 */
class CommandProcessor implements ProcessorInterface
{
    /**
     * A map of commands from the Command Bag to Orient SQL
     * @var array
     */
    protected $commandsMap = [
        Bag::COMMAND_CREATE => 'INSERT',
        Bag::COMMAND_RETRIEVE => 'SELECT',
        Bag::COMMAND_UPDATE => 'UPDATE',
        Bag::COMMAND_DELETE => 'DELETE',
    ];

    /**A map of operators from the Command Bag to Orient SQL
     * @var array
     */
    public $operatorsMap = [
        Bag::COMPARATOR_EQUAL => '=',
        Bag::COMPARATOR_GT => '>',
        Bag::COMPARATOR_LT => '<',
        Bag::COMPARATOR_LE => '<=',
        Bag::COMPARATOR_GE => '>=',
        Bag::COMPARATOR_NE => '<>',

        Bag::CONJUNCTION_AND => 'AND',
        Bag::CONJUNCTION_OR => 'OR',
    ];

    /** @var  Bag The CommandBag to be processed */
    protected $bag;

    /** @var  string The script in process */
    protected $script;

    /**
     * Command Processor
     *
     * Receives a Commands\Bag instance and returns a valid
     * Commands\CommandInterface instance with a native command
     * script for whichever driver is specified
     *
     * @param Bag $bag
     * @return CommandInterface
     */
    public function process(Bag $bag)
    {
        $this->init($bag);

        // Process the command using select(), insert(), update(), delete()
        call_user_func([$this, $this->getBagsCommand()]);

        $command = new Command($this->script);
        $command->setScriptLanguage('OrientSQL');
        return $command;
    }

    /**
     * Process a COMMAND_CREATE bag
     * @throws \Exception
     */
    public function insert()
    {
        /* CREATE VERTEX */
        $this->startScript("INSERT INTO");

        /* Users */
        $this->appendTarget("");

        /* CONTENT {} */
        $this->appendData("CONTENT");
        $this->addToScript("RETURN @this");
    }

    /**
     * Process a COMMAND_RETRIEVE bag
     * @throws NotSupportedException
     */
    public function select()
    {
        /* SELECT */
        $this->startScript("SELECT");

        /* name, username */
        $this->appendProjections();

        /* FROM Users */
        $this->appendTarget("from");

        /* WHERE last_name = 'wilson' */
        $this->appendWheres();

        /* GROUP BY country */
        $this->appendGroupBy();

        /* ORDER BY date_joined ASC */
        $this->appendOrderBy();

        /* LIMIT 20 */
        $this->appendLimit();
    }

    /**
     * Process a COMMAND_UPDATE bag
     * @throws \Exception
     */
    protected function update()
    {
        /* UPDATE */
        $this->startScript("UPDATE");

        /* Users */
        $this->appendTarget("");

        /* MERGE {} */
        $this->appendData("MERGE");

        /* WHERE */
        $this->appendWheres();

        /* LIMIT */
        $this->appendLimit();

        /* RETURN AFTER */
        $this->addToScript("RETURN AFTER");
    }

    /**
     * Process a COMMAND_DELETE bag
     */
    protected function delete()
    {
        /* DELETE VERTEX */
        $this->startScript("DELETE VERTEX");

        /* #12:1 | FROM Users */
        $this->appendTarget(($this->bag->target instanceof TargetID) ? "" : "FROM");

        /* WHERE */
        $this->appendWheres();

        /* LIMIT */
        $this->appendLimit();
    }

    /**
     * Cast a value from the Command Bag to one
     * usable by Orient SQL (a string)
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

    /**
     * Map a Command Bag operator to its Orient SQL counterpart
     * @param $operator
     * @return mixed
     */
    public function toSqlOperator($operator)
    {
        return $this->operatorsMap[$operator];
    }

    /**
     * Initialize the Command Processor
     * @param Bag $bag
     */
    public function init(Bag $bag)
    {
        $this->bag = $bag;
        $this->script = '';
    }

    /**
     * Begin the current script without a space
     * @param $clause
     */
    public function startScript($clause)
    {
        $this->script = $clause;
    }

    /**
     * Add to the current script with a space before
     * @param $clause
     * @throws \Exception
     */
    public function addToScript($clause)
    {
        if (!is_string($clause)) {
            throw new \Exception("Only strings can be added to script");
        }

        $this->script .= " " . $clause;
    }

    /**
     * Append projections to current script
     * @throws \Exception
     */
    protected function appendProjections()
    {
        if (!empty($this->bag->projections)) {
            $this->addToScript(implode(", ", $this->bag->projections));
        }
    }

    /**
     * Append target to current script
     * @param string $prefix
     * @throws \Exception
     */
    protected function appendTarget($prefix = "from")
    {
        if ($this->bag->target instanceof TargetID) {
            $target = $this->bag->target->id;
        } else {
            $target = $this->bag->target;
        }

        if ($prefix !== "") {
            $this->addToScript(strtoupper($prefix));
        }

        $this->addToScript($target);
    }

    /**
     * Append where constraints to current script
     * @throws \Exception
     */
    protected function appendWheres()
    {
        if (!empty($this->bag->where)) {
            $this->addToScript("WHERE");

            foreach ($this->bag->where as $index => $value) {
                if ($index !== 0) { // don't add conjunction to the first clause
                    $this->addToScript((string)$this->toSqlOperator($value[3]));
                }

                $this->addToScript((string)$value[0]); // field
                $this->addToScript((string)$this->toSqlOperator($value[1])); // operator
                $this->addToScript($this->castValue($value[2])); // value
            }
        }
    }

    /**
     * Append Group By to current script
     * @throws NotSupportedException
     * @throws \Exception
     */
    protected function appendGroupBy()
    {
        if (is_array($this->bag->groupBy)) {
            // Perform compliance Check
            if (count($this->bag->groupBy) > 1) {
                throw new NotSupportedException("Orient DB only allows one field in Group By");
            }

            $this->addToScript("GROUP BY");
            $this->addToScript(implode(",", $this->bag->groupBy));
        }
    }

    /**
     * Append OrderBy to current script
     * @throws NotSupportedException
     * @throws \Exception
     */
    protected function appendOrderBy()
    {
        if (is_array($this->bag->orderBy)) {
            // Perform compliance check
            if (count($this->bag->orderBy) > 1) {
                throw new NotSupportedException("Orient DB only allows one field in Order By");
            }

            $this->addToScript("ORDER BY");
            $this->addToScript(implode(",", $this->bag->orderBy));
            $this->addToScript(($this->bag->orderAsc) ? 'ASC' : 'DESC');
        }
    }

    /**
     * Append Limit to current script
     * @throws \Exception
     */
    protected function appendLimit()
    {
        if ($this->bag->limit) {
            $this->addToScript("LIMIT " . (string)$this->bag->limit);
        }
    }

    /**
     * Append data to current script
     * @param string $prefix
     * @throws \Exception
     */
    protected function appendData($prefix = "content")
    {
        $this->addToScript(strtoupper($prefix));
        $this->addToScript(json_encode($this->bag->data));
    }

    /**
     * Returns the desired command (select, update, insert, delete)
     * @return mixed
     */
    protected function getBagsCommand()
    {
        return $this->commandsMap[$this->bag->command];
    }
}
