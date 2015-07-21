<?php
namespace Michaels\Spider\Drivers;

use Michaels\Spider\Commands\CommandInterface;

/**
 * Driver contract
 */

interface DriverInterface
{
    /**
     * Sets the credentials from a properties array
     *
     * Satisfied by Michaels\Spider\Drivers\AbstractDriver
     *
     * @param array $properties
     */
    public function setCredentials(array $properties = []);

    /**
     * Sets and individual credential configuration item
     *
     * Satisfied by Michaels\Spider\Drivers\AbstractDriver
     *
     * @param $property
     * @param $value
     * @return $this
     */
    public function setCredential($property, $value);

    /**
     * Returns an individual configuration item or fallback
     *
     * Throws exception if nothing is found and no fallback
     *
     * Satisfied by Michaels\Spider\Drivers\AbstractDriver
     *
     * @param $property
     * @param null $fallback
     * @return null
     */
    public function getCredential($property, $fallback = null);

    /**
     * Connect to the database using already set, internal credentials
     * @return $this
     */
    public function open();

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
