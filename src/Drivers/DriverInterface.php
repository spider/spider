<?php
namespace Michaels\Spider\Drivers;

use Michaels\Spider\Queries\QueryInterface;

/**
 * Driver contract
 */

interface DriverInterface
{
    /**
     * Connect to the database
     *
     * @param array $properties credentials
     * @return $this
     */
    public function open(array $properties);

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
     * @param QueryInterface $query
     * @return array|Record|Graph
     */
    public function executeReadCommand(QueryInterface $query);

    /**
     * Executes a write command
     *
     * These are the "CUD" in CRUD
     *
     * @param QueryInterface $query
     * @return Graph|Record|array|mixed mixed values for some write commands
     */
    public function executeWriteCommand(QueryInterface $query);

    /**
     * Executes a read command without waiting for a response
     *
     * @param QueryInterface $query
     * @return $this
     */
    public function runReadCommand(QueryInterface $query);

    /**
     * Executes a write command without waiting for a response
     *
     * @param QueryInterface $query
     * @return $this
     */
    public function runWriteCommand(QueryInterface $query);

//    public function startTransaction();
//
//    public function stopTransaction();
}
