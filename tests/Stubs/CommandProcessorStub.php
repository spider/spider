<?php
namespace Spider\Test\Stubs;

use Spider\Commands\Bag;
use Spider\Commands\Command;
use Spider\Commands\ProcessorInterface;

/**
 * Class CommandProcessorStub
 * @package Spider\Test\Stubs
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
