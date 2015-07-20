<?php
namespace Michaels\Spider\Commands;

/**
 * Command Processor Contract
 * To be implemented by drivers
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
     * @param Bag $bag
     * @return CommandInterface
     */
    public function process(Bag $bag);
}
