<?php
namespace Spider\Drivers;

use Spider\Commands\CommandInterface;

/**
 * Driver contract
 */
interface DriverInterface
{
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
     * @return Response
     */
    public function executeReadCommand(CommandInterface $query);

    /**
     * Executes a write command
     *
     * These are the "CUD" in CRUD
     *
     * @param CommandInterface $command
     * @return Response
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
     * Format a raw response to a set of collections
     * This is for cases where a set of Vertices or Edges is expected in the response
     *
     * @param mixed $response the raw DB response
     *
     * @return Response Spider consistent response
     */
    public function formatAsSet($response);

    /**
     * Format a raw response to a tree of collections
     * This is for cases where a set of Vertices or Edges is expected in tree format from the response
     *
     * @param mixed $response the raw DB response
     *
     * @return Response Spider consistent response
     */
    public function formatAsTree($response);

    /**
     * Format a raw response to a path of collections
     * This is for cases where a set of Vertices or Edges is expected in path format from the response
     *
     * @param mixed $response the raw DB response
     *
     * @return Response Spider consistent response
     */
    public function formatAsPath($response);


    /**
     * Format a raw response to a scalar
     * This is for cases where a scalar result is expected
     *
     * @param mixed $response the raw DB response
     *
     * @return mixed Scalar value
     */
    public function formatAsScalar($response);
}
