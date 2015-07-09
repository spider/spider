<?php
namespace Michaels\Spider\Drivers;

use Michaels\Spider\Commands\CommandInterface;

/**
 * Driver contract
 */

interface DriverInterface
{
    /**
     * Connect to the database
     *
     * @param array $credentials
     * @param array $config
     * @return $this
     */
    public function open(array $credentials, array $config = []);

    /**
     * Close the database connection
     * @return $this
     */
    public function close();

    /**
     * Executes a Query or read command
     *
     * This is the R in CRUD
     *
     * @param CommandInterface $query
     * @return array|Record|Graph
     */
    public function executeReadCommand(CommandInterface $query);

    /**
     * Executes a write command
     *
     * These are the "CUD" in CRUD
     *
     * @param CommandInterface $command
     * @return Graph|Record|array|mixed mixed values for some write commands
     */
    public function executeWriteCommand(CommandInterface $command);

    /**
     * Executes a read command without waiting for a response
     *
     * @param CommandInterface $query
     * @return $this
     */
    public function runReadCommand(CommandInterface $query);

    /**
     * Executes a write command without waiting for a response
     *
     * @param CommandInterface $command
     * @return $this
     */
    public function runWriteCommand(CommandInterface $command);

//    public function startTransaction();
//
//    public function stopTransaction();
}
