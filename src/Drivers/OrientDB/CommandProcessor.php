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
                if ($index !== 0) { // dont add conjunction to the first clause
                    $script .= " $value[3]";
                }

                $script .= " $value[0] $value[1] " . $this->castValue($value[2]);
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
//        if ($value === true) {
//            $value = 'true';
//
//        } elseif ($value === false) {
//            $value = 'false';
//
//        } elseif (is_string($value)) {
//            $value = "'$value'";
//        }
        return (string)$value;
    }
}
