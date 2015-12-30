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
class CommandProcessor extends  AbstractOrientSqlProcessor
{
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

    /* This is a dummy process to return the results I want implicitly */
//    public function process(Bag $bag)
//    {
//        if (count($bag->create) === 1) {
////            $query = "begin\n";
////            $query .= "let t1 = CREATE EDGE knows FROM (SELECT FROM V WHERE name = 'peter') TO (SELECT FROM V WHERE name ='josh')";
////            $query .= "\ncommit retry 100\n";
////            $query .= 'return $t1';
////
////            return $query;
//            return $this->processInternal($bag);
//        } else {
//            $query = "begin\n";
//            $query .= "let t1 = CREATE VERTEX person CONTENT {name: 'michael'}";
//            $query .= "let t2 = CREATE VERTEX person CONTENT {name: 'dylan'}";
//            $query .= "let t3 = CREATE EDGE knows FROM (SELECT FROM V WHERE name = 'michael') TO (SELECT FROM V WHERE name ='dylan')";
//            $query .= "\ncommit retry 100\n";
//            $query .= 'return $t1';
//
//            return $query;
//        }
//    }

    public function process(Bag $bag, $embedded = false)
    {


        /*
         * Order is important:
         *      Retrieve first
         *      Create second
         *      Update third (uses modifiers)
         *      Delete fourth  (uses modifiers)
         */

        /* Decide Which Processor To Use Based On Scenarios */
        //
        if ($this->isSimpleSelect($bag)) {
            $command = (new SimpleSelect())->process($bag);
            return $this->ensureBatchScriptIfNeeded($embedded, $command);
        }

        if ($this->isSimpleCreate($bag)) {
            $command = (new SimpleCreate())->process($bag);
            return $this->ensureBatchScriptIfNeeded($embedded, $command);
        }

        // A Simple Update Scenario

        // A Simple Delete Scenario

        // Ensure the Command is an OrientBatch SQL Script

        // We are left with a multiple operation scenario
        return (new MultipleOperation())->process($bag);
    }

    /**
     * @param $embedded
     * @param $command
     * @return mixed
     */
    protected function ensureBatchScriptIfNeeded($embedded = false, Command $command)
    {
        if ($embedded) {
            return $command;
        }

        $batchScript = new SqlBatch();
        $batchScript->begin();
        $batchScript->addStatement($command->getScript());
        $batchScript->end();

        $command->setScript($batchScript->getScript());

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
//
//    /**
//     * Returns the desired command (select, update, insert, delete)
//     * @return mixed
//     */
//    protected function getBagsCommand()
//    {
//        return $this->commandsMap[$this->bag->command];
//    }
}
