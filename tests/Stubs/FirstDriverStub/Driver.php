<?php
namespace Spider\Test\Stubs\FirstDriverStub;

use Spider\Commands\CommandInterface;
use Spider\Drivers\AbstractDriver;
use Spider\Drivers\DriverInterface;
use Spider\Graphs\Record;

class Driver extends AbstractDriver implements DriverInterface
{

    protected function returnData()
    {
        return new Record([
            'one' => 1,
            'two' => true,
            'three' => 'three',
        ]);
    }

    /**
     * Connect to the database
     * @return $this
     */
    public function open()
    {
        return $this;
    }

    /**
     * Close the database connection
     * @return $this
     */
    public function close()
    {
        return $this;
    }

    /**
     * Executes a Query or read command
     *
     * This is the R in CRUD
     *
     * @param CommandInterface $query
     * @return array|Record|\Spider\Drivers\Graph
     */
    public function executeReadCommand(CommandInterface $query)
    {
        return $this->returnData();
    }

    /**
     * Executes a write command
     *
     * These are the "CUD" in CRUD
     *
     * @param CommandInterface $command
     * @return \Spider\Drivers\Graph|Record|array|mixed mixed values for some write commands
     */
    public function executeWriteCommand(CommandInterface $command)
    {
        return $this->returnData();
    }

    /**
     * Executes a read command without waiting for a response
     *
     * @param CommandInterface $command
     * @return $this
     */
    public function runReadCommand(CommandInterface $command)
    {
        return $this;
    }

    /**
     * Executes a write command without waiting for a response
     *
     * @param CommandInterface $query
     * @return $this
     */
    public function runWriteCommand(CommandInterface $query)
    {
        return $this;
    }
}
