<?php
namespace Michaels\Spider\Commands;

interface ProcessorInterface
{
    /**
     * Process Query
     *
     * @param Bag $bag
     * @return string
     */
    public function process(Bag $bag);
}
