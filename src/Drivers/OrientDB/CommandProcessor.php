<?php
namespace Michaels\Spider\Drivers\OrientDB;

use Michaels\Spider\Commands\Bag;
use Michaels\Spider\Commands\Command;
use Michaels\Spider\Commands\ProcessorInterface;

/**
 * Class QueryProcessor
 * @package Michaels\Spider\Drivers\OrientDB
 */
class CommandProcessor implements ProcessorInterface
{

    protected $commands = [
        'select' => 'SELECT'
    ];

    public $token = [
        Bag::COMPARATOR_EQUAL => '=',
        Bag::COMPARATOR_GT => '>',
        Bag::COMPARATOR_LT => '<',
        Bag::COMPARATOR_LE => '<=',
        Bag::COMPARATOR_GE => '>=',
        Bag::COMPARATOR_NE => '<>',

        Bag::CONJUNCTION_AND => 'AND',
        Bag::CONJUNCTION_OR => 'OR',
    ];

    public function toToken($operator)
    {
        return $this->token[$operator];
    }

    /**
     * Process Query
     *
     * @param Bag $bag
     * @return string
     */
    public function process(Bag $bag)
    {
        // NOTE: the whitespace should be placed by the new clause at beginning, not the previous clause at the end

        // COMMAND
        $script = $this->commands[$bag->command];

        // <projections>
        if (!empty($bag->projections)) {
            $script .= " " . trim(implode(", ", $bag->projections), ", ");
        }

        // FROM
        $script .= " FROM " . $bag->from;

        // WHERE
        if (!empty($bag->where)) {
            $script .= " WHERE";

            foreach ($bag->where as $index => $value) {
                if ($index !== 0) { // don't add conjunction to the first clause
                    $script .= " " . (string)$this->toToken($value[3]);
                }

                $script .= " " . (string)$value[0]; // field
                $script .= " " . (string)$this->toToken($value[1]); // operator
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
