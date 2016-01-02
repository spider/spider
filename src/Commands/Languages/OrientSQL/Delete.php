<?php
namespace Spider\Commands\Languages\OrientSQL;
use Spider\Commands\Bag;

/**
 * Class SimpleCreate
 * @package Spider\Commands\Languages\OrientSQL
 */
class Delete extends AbstractOrientSqlProcessor
{

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
        $statements = [];

        $type = Bag::ELEMENT_VERTEX;
        foreach ($bag->where as $constraint) {
            if ($constraint[0] === Bag::ELEMENT_TYPE) {
                $type = $constraint[2];
                break;
            }
        }

        switch ($type) {
            case (Bag::ELEMENT_EDGE):
                $statements[] = $this->deleteEdge();
                break;

            case (Bag::ELEMENT_VERTEX):
                $statements[] = $this->deleteVertex();
                break;
        }

        return $statements;
    }

    /**
     * Process a COMMAND_CREATE bag
     * @return mixed
     * @throws \Exception
     * @internal param array $record
     */
    protected function deleteEdge()
    {
        throw new \Exception("This should not be used yet");
    }

    /**
     * Process a COMMAND_CREATE bag
     * @return mixed
     * @internal param array $record
     */
    protected function deleteVertex()
    {
        $script = '';
        $this->startScript("DELETE VERTEX", $script);

        /* Users */
        $this->appendClass("V", $this->bag->where, $script);

        $this->appendWheres($this->bag->where, $script);

        $this->appendLimit($this->bag, $script);

        /* Finished */
        return $script;
    }
}
