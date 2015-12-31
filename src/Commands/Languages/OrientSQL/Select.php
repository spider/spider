<?php
namespace Spider\Commands\Languages\OrientSQL;
use Spider\Commands\Bag;
use Spider\Exceptions\NotSupportedException;

/**
 * Class SimpleSelect
 * @package Spider\Commands\Languages\OrientSQL
 */
class Select extends AbstractOrientSqlProcessor
{
    protected $script = '';

    /**
     * Command Processor
     *
     * Receives a Commands\Bag instance and returns a valid
     * Commands\CommandInterface instance with a native command
     * script for whichever driver is specified
     *
     * @param \Spider\Commands\Bag $bag
     * @return array
     */
    public function process(Bag $bag)
    {
        $this->init($bag);
        $this->validateBag();

        $this->processSelect();

        return [$this->getScript()]; //$this->createCommand($this->getScript());
    }

    /**
     * Process a COMMAND_RETRIEVE bag
     * @throws NotSupportedException
     */
    public function processSelect()
    {
        /* SELECT */
        $this->startScript("SELECT", $this->script);

        /* name, username */
        $this->appendProjections();

        /* FROM Users */
        $this->appendTarget("from", $this->script);

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
     * Append projections to current script
     * @throws \Exception
     */
    protected function appendProjections()
    {
        if (!empty($this->bag->retrieve)) {
            $this->addToScript(implode(", ", $this->bag->retrieve), $this->script);
        }
    }

    /**
     * Append where constraints to current script
     * @throws \Exception
     */
    protected function appendWheres()
    {
        $wheres = '';
        foreach ($this->bag->where as $index => $value) {
            /* Skip the Element Type, not needed for Orient */
            if ($value[0] === Bag::ELEMENT_TYPE) {
                continue;
            }

            if ($index !== 0) { // don't add conjunction to the first clause
                $wheres .= " " . (string)$this->toSqlOperator($value[3]);
            }

            $wheres .= " " . (string)$value[0]; // field
            $wheres .= " " . (string)$this->toSqlOperator($value[1]); // operator
            $wheres .= " " . $this->castValue($value[2]); // value
        }

        if ($wheres !== '') {
            $this->addToScript("WHERE", $this->script);
            $this->addToScript(ltrim($wheres), $this->script);
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

            $this->addToScript("GROUP BY", $this->script);
            $this->addToScript(implode(",", $this->bag->groupBy), $this->script);
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
            $this->addToScript("ORDER BY", $this->script);

            $orders = [];
            foreach ($this->bag->orderBy as $field) {
                $orders[] = "$field[0] " . $this->toSqlOperator($field[1]);
            }

            $this->addToScript(implode(", ", $orders), $this->script);
        }
    }

    /**
     * Append Limit to current script
     * @throws \Exception
     */
    protected function appendLimit()
    {
        if ($this->bag->limit) {
            $this->addToScript("LIMIT " . (string)$this->bag->limit, $this->script);
        }
    }

    /**
     * @return mixed
     */
    public function getScript()
    {
        return $this->script;
    }
}
