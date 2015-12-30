<?php
namespace Spider\Commands\Languages\OrientSQL;
use Spider\Commands\Bag;

/**
 * Class SimpleCreate
 * @package Spider\Commands\Languages\OrientSQL
 */
class SimpleCreate extends AbstractOrientSqlProcessor
{

    /**
     * Command Processor
     *
     * Receives a Commands\Bag instance and returns a valid
     * Commands\CommandInterface instance with a native command
     * script for whichever driver is specified
     *
     * @param \Spider\Commands\Bag $bag
     * @return \Spider\Commands\Command
     */
    public function process(Bag $bag)
    {
        $this->init($bag);
        $this->validateBag();

        $this->processInsert();

        return $this->createCommand($this->getScript());
    }

    /**
     * Process a COMMAND_CREATE bag
     * @throws \Exception
     */
    public function processInsert()
    {
        /* CREATE VERTEX */
        $this->startScript("INSERT INTO");

        /* Users */
        $this->appendTarget("");

        /* CONTENT {} */
        $this->appendInsertData();
        $this->addToScript("RETURN @this");

        return $this->getScript();
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
        if (count($this->bag->create) > 1) {
            // First, we setup the keys array [key1, key2, key3]
            foreach ($this->bag->create as $record) {
                $keys = array_unique(array_merge($keys, array_keys($record)));
            }

            // Now we setup sets of values arrays ['one', null, 'two'], [null, 'three', 'four']
            $i = 0;

            // For every record
            foreach ($this->bag->create as $record) {
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
            $keys = array_keys($this->bag->create[0]);
            $values = array_values($this->bag->create[0]);

            $values = array_map(function ($value) {
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
}
