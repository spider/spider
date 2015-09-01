<?php
namespace Spider\Commands\Languages\OrientSQL;

use Spider\Commands\Bag;
use Spider\Commands\Command;
use Spider\Commands\CommandInterface;
use Spider\Commands\Languages\ProcessorInterface;
use Spider\Exceptions\NotSupportedException;

/**
 * Class CommandProcessor
 * OrientSQL implementation
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

        Bag::ORDER_DESC => 'DESC',
        Bag::ORDER_ASC => 'ASC',
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
        $command->setScriptLanguage('orientSQL');

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
        $this->appendInsertData();
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
        $this->appendUpdateData();

        /* RETURN AFTER */
        $this->addToScript("RETURN AFTER");

        /* WHERE */
        $this->appendWheres();

        /* LIMIT */
        $this->appendLimit();
    }

    /**
     * Process a COMMAND_DELETE bag
     */
    protected function delete()
    {
        /* DELETE VERTEX */
        $this->startScript("DELETE VERTEX");

        /* #12:1 | FROM Users */
        foreach ($this->bag->where as $index => $where) {
            if ($where[0] === Bag::ELEMENT_LABEL) {
                $this->addToScript("FROM $where[2]");
                unset($this->bag->where[$index]);
                $this->bag->where = array_values($this->bag->where);
                break;

            } elseif ($where[0] === Bag::ELEMENT_ID) {
                $this->addToScript("V");
                if (!is_array($where[2])) {
                    $where[2] = [$where[2]];
                }

                foreach ($where[2] as $id) {
                    if (!is_string($id)) {
                        throw new \Exception("ids can only be ids. $id given");
                    }
                    $this->bag->where[] = ['@rid', Bag::COMPARATOR_EQUAL, $id, Bag::CONJUNCTION_OR];
                }

                unset($this->bag->where[$index]);
                $this->bag->where = array_values($this->bag->where);
                break;
            }
        }

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
     * @param string $clause
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
        // Set the vertex or edge target
        $target = ($this->bag->target === Bag::ELEMENT_EDGE) ? 'E' : 'V';

        // Search through wheres for label (class) or id
        foreach ($this->bag->data as $index => $value) {
            if (isset($value[Bag::ELEMENT_LABEL])) {
                $target = $value[Bag::ELEMENT_LABEL];
                unset($this->bag->data[$index][Bag::ELEMENT_LABEL]);
                $this->bag->data = array_values($this->bag->data);
            }
        }

        // Search through wheres for label (class) or id
        foreach ($this->bag->where as $index => $value) {
            if ($value[0] === Bag::ELEMENT_LABEL || $value[0] === Bag::ELEMENT_ID) {
                $target = $value[2];
                unset($this->bag->where[$index]);
                $this->bag->where = array_values($this->bag->where);
            }
        }

        if ($prefix !== "") {
            $this->addToScript(strtoupper($prefix)); // FROM
        }
        $this->addToScript($target); // ID or Class
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
            $this->addToScript("ORDER BY");

            $orders = [];
            foreach ($this->bag->orderBy as $field) {
                $orders[] = "$field[0] " . $this->toSqlOperator($field[1]);
            }

            $this->addToScript(implode(", ", $orders));
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
     * Append insert data to current script
     *
     * @throws \Exception
     */
    protected function appendInsertData()
    {
        $keys = [];
        $values = [];

        /* Is this a multiple creation? */
        if (count($this->bag->data) > 1) {
            // First, we setup the keys array [key1, key2, key3]
            foreach ($this->bag->data as $record) {
                $keys = array_unique(array_merge($keys, array_keys($record)));
            }

            // Now we setup sets of values arrays ['one', null, 'two'], [null, 'three', 'four']
            $i = 0;

            // For every record
            foreach ($this->bag->data as $record) {
                // We check every key
                $set = [];
                foreach ($keys as $key) {
                    // And set it to a value
                    if (array_key_exists($key, $record)) {
                        $set[] = $this->castValue($record[$key]);

                        // Or to 'null'
                    } else {
                        $set[] = 'null';
                    }
                }

                // Create the string for that value set
                $values[$i] = '(' . implode(", ", $set) . ')';
                $i++;
            }

            /* No, its a single creation */
        } else {
            $keys = array_keys($this->bag->data[0]);
            $values = array_values($this->bag->data[0]);

            $values = array_map(function($value) {
                return $this->castValue($value);
            }, $values);
        }

        $stringValues = '(' . implode(", ", $values) . ')';
        $stringValues = str_replace("((", "(", $stringValues);
        $stringValues = str_replace("))", ")", $stringValues);

        $stringKeys = implode(", ", $keys);

        $data = "($stringKeys) VALUES $stringValues";
        $this->addToScript($data);
    }

    /**
     * Append update data to current script
     * @throws \Exception
     */
    protected function appendUpdateData()
    {
        $this->addToScript("MERGE");
        $this->addToScript(json_encode($this->bag->data[0]));
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
