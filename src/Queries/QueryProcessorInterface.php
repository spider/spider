<?php
namespace Michaels\Spider\Queries;

interface QueryProcessorInterface
{
    /**
     * Process Query
     *
     * @param Bag $bag
     * @return string
     */
    public function process(Bag $bag);
}
