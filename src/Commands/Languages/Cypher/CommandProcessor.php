<?php
namespace Spider\Commands\Languages\Cypher;

use Spider\Commands\Bag;
use Spider\Commands\Command;
use Spider\Commands\CommandInterface;
use Spider\Commands\Languages\ProcessorInterface;
use Spider\Exceptions\NotSupportedException;

/**
 * Class CommandProcessor
 * Cypher implementation
 */
class CommandProcessor implements ProcessorInterface
{
    /**
     * A map of commands from the Command Bag to Orient SQL
     * @var array
     */
    protected $commandsMap = [
        Bag::COMMAND_CREATE => 'CREATE',
        Bag::COMMAND_RETRIEVE => 'MATCH',
        Bag::COMMAND_UPDATE => 'SET',
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
        Bag::COMPARATOR_IN => 'IN',

        Bag::CONJUNCTION_AND => 'AND',
        Bag::CONJUNCTION_OR => 'OR',
    ];

    /** @var  Bag The CommandBag to be processed */
    protected $bag;

    /** @var  string The script in process */
    protected $script;

    protected $variables = [];

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
        $command->setScriptLanguage('cypher');

        $this->reset();
        return $command;
    }

    /**
     * Process a COMMAND_CREATE bag
     * @throws \Exception
     */
    public function create()
    {
        // Let generate all the rows we need
        $traversals = [];
        $sets = [];
        foreach ($this->bag->data as $data) {
            $traversals[] = $this->buildTraversal();
            $sets[] = $this->buildSet($data);
        }

        /* Create elements */
        $this->startScript("CREATE");
        $variable = implode(', ', $traversals);
        $this->addToScript($variable);

        /* set/update created elements */
        $this->addToScript("SET");
        $this->addToScript(implode(', ', $sets));

        /* Return clause */
        $this->addToScript('RETURN');
        $this->addToScript($this->buildProjections());
    }

    /**
     * Process a COMMAND_RETRIEVE bag
     * @throws NotSupportedException
     */
    public function match()
    {
        /* match */
        $this->startScript("MATCH");
        $this->addToScript($this->buildTraversal());

        /* WHERE last_name = 'wilson' */
        if (!empty($this->bag->where)) {
            $this->addToScript('WHERE');
            $this->addToScript($this->buildWheres());
        }

        /* Return clause */
        $this->addToScript('RETURN');
        $this->addToScript($this->buildProjections());

        /* ORDER BY date_joined ASC */
        if (!empty($this->bag->orderBy)) {
            $this->addToScript("ORDER BY");
            $this->addToScript($this->buildOrderBy());
        }

        /* LIMIT 20 */
        $this->appendLimit();
    }

    /**
     * Process a COMMAND_UPDATE bag
     * @throws \Exception
     */
    protected function set()
    {
        /* DELETE VERTEX */
        $this->startScript("MATCH");
        $this->addToScript($this->buildTraversal());

        /* name, username */
        //~ $this->appendProjections();

        /* WHERE last_name = 'wilson' */
        if (!empty($this->bag->where)) {
            $this->addToScript('WHERE');
            $this->addToScript($this->buildWheres());
        }

        /* SET clause */
        $this->addToScript("SET");
        $this->addToScript($this->buildSet($this->bag->data[0]));

        /* Return clause */
        $this->addToScript('RETURN');
        $this->addToScript($this->buildProjections());

        /* ORDER BY date_joined ASC */
        if (!empty($this->bag->orderBy)) {
            $this->addToScript("ORDER BY");
            $this->addToScript($this->buildOrderBy());
        }

        /* LIMIT 20 */
        $this->appendLimit();
    }

    /**
     * Process a COMMAND_DELETE bag
     */
    protected function delete()
    {
        /* MATCH CLAUSE*/
        $this->startScript("MATCH");
        $this->addToScript($this->buildTraversal());

        /* WHERE clause*/
        if (!empty($this->bag->where)) {
            $this->addToScript('WHERE');
            $this->addToScript($this->buildWheres());
        }

        /* ORDER BY date_joined ASC */
        if (!empty($this->bag->orderBy)) {
            $this->addToScript("ORDER BY");
            $this->addToScript($this->buildOrderBy());
        }
        /* LIMIT 20 */
        $this->appendLimit();

        /* Delete clause */
        $this->addToScript('DELETE');
        $this->addToScript($this->buildProjections());
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
        } elseif (is_string($value) && !is_int($value)) {
            $value = "'$value'";
        } elseif (is_array($value)) {
            $value = json_encode($value);
        }

        return (string)$value;
    }

    /**
     * Map a Command Bag operator to its Orient SQL counterpart
     * @param $operator
     * @return mixed
     */
    public function toCypherOperator($operator)
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
     * @param string $clause
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
    protected function buildProjections()
    {
        if (!empty($this->bag->projections)) {
            $projections = [];
            foreach ($this->bag->projections as $projection) {
                $projections[] = $this->detailField($projection);
            }
            $return = implode(", ", $projections);
        } else {
            $return = implode(", ", $this->variables);
        }

        return $return;
    }

    /**
     * Append where constraints to current script
     * @throws \Exception
     */
    protected function buildWheres()
    {
        $where = [];
        foreach ($this->bag->where as $index => $value) {
            if ($index !== 0) { // don't add conjunction to the first clause
                $where[] = (string)$this->toCypherOperator($value[3]);
            }

            switch ($value[0]) {
                case Bag::ELEMENT_LABEL:
                    // @todo change to use appropriate variable when traversals are added.
                    $where[] = end($this->variables) . ':' . $value[2];
                    break;
                case Bag::ELEMENT_ID:
                    $idWhere = 'ID(' . end($this->variables) . ') ' . (string)$this->toCypherOperator($value[1]) . ' ';
                    $idWhere .= (is_array($value[2]) ? $this->castValue($value[2]) : $value[2]);
                    $where[] = $idWhere;
                    break;
                default:
                    $where[] = (string)$this->detailField($value[0]) . ' ' . (string)$this->toCypherOperator($value[1]) . ' ' . $this->castValue($value[2]);
            }
        }
        return implode(' ', $where);
    }

    /**
     * Append OrderBy to current script
     * @throws NotSupportedException
     * @throws \Exception
     */
    protected function buildOrderBy()
    {
        // Perform compliance check
        if (!$this->bag->orderBy) {
            return '';
        }

        $orders = [];
        foreach ($this->bag->orderBy as $order) {
            $direction = ($order[1] === Bag::ORDER_ASC) ? 'ASC' : 'DESC';
            if (strpos($order[0], '.') === false) {
                $orders[] = $this->detailField($order[0]) . ' ' . $direction;
            } else {
                $orders[] = $order[0] . ' ' . $direction;
            }
        }
        return implode(",", $orders);
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
     * Append update data to current script
     * @param $data
     * @return string
     * @throws NotSupportedException
     */
    protected function buildSet($data)
    {
        $set = [];
        foreach ($data as $key => $value) {
            switch ($key) {
                case Bag::ELEMENT_LABEL:
                    // @todo change to use appropriate variable when traversals are added.
                    $set[] = end($this->variables) . ' :' . $value;
                    break;
                case Bag::ELEMENT_ID:
                    throw new NotSupportedException('Neo4J will not allow you to manually set IDs');
                default:
                    $set[] = $this->detailField($key) . ' = ' . $this->castValue($value);
            }
        }
        return implode(', ', $set);
    }

    /**
     * Append traversal data to current script
     * @return string
     */
    protected function buildTraversal()
    {
        $var = $this->generateVar();
        if ($this->bag->target == Bag::ELEMENT_EDGE) {
            //We're trying to select an edge
            return "()-[{$var}]-()";

        } else {
            //We're looking for vertices
            return "({$var})";
        }
    }

    /**
     * Returns the desired command (select, update, insert, delete)
     * @return mixed
     */
    protected function getBagsCommand()
    {
        return $this->commandsMap[$this->bag->command];
    }

    /**
     * generates a random var
     *
     * @return string
     */
    protected function generateVar()
    {
        $var = "a";
        $searching = true;

        while ($searching) {
            if (in_array('spider_' . $var, $this->variables)) {
                $var++;
            } else {
                $this->variables[] = 'spider_' . $var;
                $searching = false;
            }
        }
        return 'spider_' . $var;
    }

    /**
     * adds the correct prefix for variables that were'nt specified as belonging to a specific set.
     *
     * @param the field to check for alias prefixes.
     *
     * @return string the field with appropriate prefix
     */
    protected function detailField($field)
    {
        if (strpos($field, '.') === false) {
            return end($this->variables) . '.' . $field;
        } else {
            return $field;
        }
    }

    /**
     * Resets the builder for a new query
     */
    public function reset()
    {
        $this->variables = [];
        $this->bag = null;
        $this->script = null;
    }
}
