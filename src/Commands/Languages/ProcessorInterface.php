<?php
namespace Spider\Commands\Languages;

use Spider\Commands\Bag;

/**
 * Command Processor Contract
 * To be implemented by processors
 */
interface ProcessorInterface
{
    /**
     * Command Processor
     *
     * Receives a Commands\Bag instance and returns a valid
     * Commands\CommandInterface instance with a native command
     * script for whichever driver is specified
     *
     * @param \Spider\Commands\Bag $bag
     * @return \Spider\Commands\CommandInterface
     */
    public function process(Bag $bag);
}
