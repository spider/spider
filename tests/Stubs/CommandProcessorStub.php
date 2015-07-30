<?php
namespace Spider\Test\Stubs;

use Spider\Commands\Bag;
use Spider\Commands\Command;
use Spider\Commands\Languages\ProcessorInterface;

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
        $command = new Command(json_encode($bag));
        $command->setRw('read');
        return $command;
    }
}
