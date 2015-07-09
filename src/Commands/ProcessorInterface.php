<?php
namespace Michaels\Spider\Commands;

interface ProcessorInterface
{
    /**
     * Process Query
     *
     * @param Bag $bag
     * @return CommandInterface
     */
    public function process(Bag $bag);
}
