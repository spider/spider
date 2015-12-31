<?php
namespace Spider\Commands\Languages\OrientSQL;

use Spider\Commands\Bag;
use Spider\Commands\Command;
use Spider\Commands\CommandInterface;
use Spider\Commands\Languages\AbstractProcessor;
use Spider\Commands\Languages\ProcessorInterface;
use Spider\Exceptions\NotSupportedException;

/**
 * Acts as a Traffic Cop, passing the Bag to the correct Processor
 * OrientSQL implementation
 * @package Spider\Drivers\OrientDB
 */
class CommandProcessor extends AbstractProcessor implements ProcessorInterface
{
    public function __construct()
    {
        $this->batch = new SqlBatch();
    }

    /**
     * Command Processor
     *
     * Receives a Commands\Bag instance and returns a valid
     * Commands\CommandInterface instance with a native command
     * script for whichever driver is specified
     *
     * @param Bag $bag
     * @param bool $embedded
     * @return Command
     */
    public function process(Bag $bag, $embedded = false)
    {
        $this->batch->begin();

        /*
         * Order is important:
         *      Retrieve first
         *      Create second
         *      Update third (uses modifiers)
         *      Delete fourth  (uses modifiers)
         */

        /* Decide Which Processor To Use Based On Scenarios */
        if ($this->isSelecting($bag)) {
            $statements = (new Select($this))->process($bag); // returns ARRAY of statements to be consistent with other Processors

            if ($embedded && count($statements) === 1) {
                return $statements[0];
            }

            $this->batch->addStatements($statements);
        }

        if ($this->isCreating($bag)) {
            $statements = (new Create($this))->process($bag); // returns ARRAY of statements to be consistent with other Processors

            if ($embedded && count($statements) === 1) {
                return $statements[0];
            }

            $this->batch->addStatements($statements);
        }

        $this->batch->end();
        return $this->createCommand($this->batch->getScript());

        // A Update Scenario

        // A Delete Scenario
    }

    /**
     * @param $script
     * @return Command
     */
    protected function createCommand($script)
    {
        $command = new Command($script);
        $command->setScriptLanguage('orientSQL');
        return $command;
    }

//
//
//    /**
//     * Process a COMMAND_UPDATE bag
//     * @throws \Exception
//     */
//    protected function update()
//    {
//        /* UPDATE */
//        $this->startScript("UPDATE");
//
//        /* Users */
//        $this->appendTarget("");
//
//        /* MERGE {} */
//        $this->appendUpdateData();
//
//        /* RETURN AFTER */
//        $this->addToScript("RETURN AFTER");
//
//        /* WHERE */
//        $this->appendWheres();
//
//        /* LIMIT */
//        $this->appendLimit();
//    }
//
//    /**
//     * Process a COMMAND_DELETE bag
//     */
//    protected function delete()
//    {
//        /* DELETE VERTEX */
//        $this->startScript("DELETE VERTEX");
//
//        /* #12:1 | FROM Users */
//        foreach ($this->bag->where as $index => $where) {
//            if ($where[0] === Bag::ELEMENT_LABEL) {
//                $this->addToScript("FROM $where[2]");
//                unset($this->bag->where[$index]);
//                $this->bag->where = array_values($this->bag->where);
//                break;
//
//            } elseif ($where[0] === Bag::ELEMENT_ID) {
//                $this->addToScript("V");
//                if (!is_array($where[2])) {
//                    $where[2] = [$where[2]];
//                }
//
//                foreach ($where[2] as $id) {
//                    if (!is_string($id)) {
//                        throw new \Exception("ids can only be ids. $id given");
//                    }
//                    $this->bag->where[] = ['@rid', Bag::COMPARATOR_EQUAL, $id, Bag::CONJUNCTION_OR];
//                }
//
//                unset($this->bag->where[$index]);
//                $this->bag->where = array_values($this->bag->where);
//                break;
//            }
//        }
//
//        /* WHERE */
//        $this->appendWheres();
//
//        /* LIMIT */
//        $this->appendLimit();
//    }
//

//
//    /**
//     * Append update data to current script
//     * @throws \Exception
//     */
//    protected function appendUpdateData()
//    {
//        $this->addToScript("MERGE");
//        $this->addToScript(json_encode($this->bag->data[0]));
//    }
}
