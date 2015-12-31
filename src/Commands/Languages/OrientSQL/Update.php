<?php
namespace Spider\Commands\Languages\OrientSQL;
use Spider\Commands\Bag;
use Spider\Exceptions\NotSupportedException;

/**
 * Class SimpleSelect
 * @package Spider\Commands\Languages\OrientSQL
 */
class Update extends AbstractOrientSqlProcessor
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

        $this->processUpdate();

        return [$this->getScript()];
    }

    /**
     * Process a COMMAND_RETRIEVE bag
     * @throws NotSupportedException
     */
    public function processUpdate()
    {
        $this->startScript("UPDATE", $this->script);

        $this->appendClass("V", $this->bag->where, $this->script);

        $this->appendContent("MERGE", $this->bag->update, $this->script);

        $this->appendReturn();

        $this->appendWheres($this->bag->where, $this->script);

        $this->appendLimit($this->bag, $this->script);
    }

    public function appendReturn()
    {
        $this->addToScript("RETURN AFTER", $this->script);
    }
}
