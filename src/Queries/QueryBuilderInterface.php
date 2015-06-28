<?php
namespace Michaels\Spider\Queries;

/**
 * This is just a placeholder for now, until work on the QueryBuilders begin
 * @package Michaels\Spider\Queries
 */
interface QueryBuilderInterface extends QueryInterface
{
    /**
     * Add a SELECT command to the query
     * @return mixed
     */
    public function select();

    /**
     * Reset the Query
     * @return mixed
     */
    public function reset();

    /**
     * Build the query into the designated script language
     * @return mixed
     */
    public function buildScript();

    // More as this is fleshed out
}
