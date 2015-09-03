<?php
namespace Spider\Connections;

use Spider\Drivers\DriverInterface;

/**
 * Facilitates two-way communication with a data-store
 * @package Spider\Test\Unit\Connections
 */
interface ConnectionInterface
{
    /**
     * Connects to the database
     * @return DriverInterface
     */
    public function open();

    /**
     * Closes database connection
     * @return DriverInterface
     */
    public function close();

    /**
     * Executes a Command
     *
     * This is the R in CRUD
     *
     * @param CommandInterface|BaseBuilder $query
     * @return Response
     */
    public function executeCommand($query);

    /**
     * Runs a Command without waiting for a response
     *
     * @param CommandInterface|BaseBuilder $command
     * @return $this
     */
    public function runCommand($command);

    /**
     * Opens a transaction
     *
     * @return bool
     */
    public function startTransaction();

    /**
     * Closes a transaction
     *
     * @param bool $commit whether this is a commit (true) or a rollback (false)
     *
     * @return bool
     */
    public function stopTransaction($commit = true);

    /**
     * Returns the class name of the active driver
     * @return string
     */
    public function getDriverName();

    /**
     * Returns the instance of the driver
     * @return DriverInterface
     */
    public function getDriver();

    /**
     * Updates the driver instance
     *
     * @param DriverInterface $driver
     * @return void
     */
    public function setDriver(DriverInterface $driver);
}
