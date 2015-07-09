<?php
namespace Michaels\Spider\Test\Stubs;
use Michaels\Spider\Commands\ProcessorInterface;
use Michaels\Spider\Commands\Bag;
use Michaels\Spider\Commands\Command;

/**
 * Class CommandProcessorStub
 * @package Michaels\Spider\Test\Stubs
 */
class CommandProcessorStub implements ProcessorInterface
{

    /**
     * Process Query
     *
     * @param Bag $bag
     * @return string
     */
    public function process(Bag $bag)
    {
        return new Command(json_encode($bag));
    }
}
