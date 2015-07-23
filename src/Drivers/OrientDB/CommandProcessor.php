<?php
namespace Spider\Drivers\OrientDB;

use Spider\Commands\Bag;
use Spider\Commands\Command;
use Spider\Commands\CommandInterface;
use Spider\Commands\ProcessorInterface;

/**
 * Class QueryProcessor
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
        Bag::COMMAND_DELETE => 'DROP',
    ];

    /**A map of opperators from the Command Bag to Orient SQL
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
    /* ToDo: Divide this into multiple methods */
    /* ToDo: Throw orient specific exceptions when needed */
    public function process(Bag $bag)
    {
        // NOTE: the whitespace should be placed by the new clause at beginning, not the previous clause at the end

        // COMMAND
//        die(var_dump($bag->command));
        $script = $this->commandsMap[$bag->command];

        // <projections>
        if (!empty($bag->projections)) {
            $script .= " " . trim(implode(", ", $bag->projections), ", ");
        }

        // FROM
        $script .= " FROM " . $bag->target;

        // WHERE
        if (!empty($bag->where)) {
            $script .= " WHERE";

            foreach ($bag->where as $index => $value) {
                if ($index !== 0) { // don't add conjunction to the first clause
                    $script .= " " . (string)$this->toSqlOperator($value[3]);
                }

                $script .= " " . (string)$value[0]; // field
                $script .= " " . (string)$this->toSqlOperator($value[1]); // operator
                $script .= " " . $this->castValue($value[2]); // value
            }
        }

        // GROUP BY
        if (is_array($bag->groupBy)) {
//
//            // Perform compliance Check
//            if (count($bag->groupBy) > 1) {
//                throw new \InvalidArgumentException("Orient DB only allows one field in Group By");
//            }

            $script .= " GROUP BY";

            foreach ($bag->groupBy as $index => $field) {
                if ($index !== 0) {
                    $script .= ",";
                }

                $script .= " $field";
            }
        }

        // ORDER BY
        if (is_array($bag->orderBy)) {
//
//            // Perform compliance check
//            if (count($bag->orderBy) > 1) {
//                throw new \InvalidArgumentException("Orient DB only allows one field in Group By");
//            }

            $script .= " ORDER BY";

            foreach ($bag->orderBy as $index => $field) {
                if ($index !== 0) {
                    $script .= ",";
                }

                $script .= " $field";
            }

            $script .= ($bag->orderAsc) ? ' ASC' : ' DESC';
        }

        // LIMIT
        if ($bag->limit) {
            $script .= " LIMIT " . (string)$bag->limit;
        }

        return new Command($script);
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
}
