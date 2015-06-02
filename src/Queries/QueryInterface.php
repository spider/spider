<?php
namespace Michaels\Spider\Queries;

/**
 * Contract for Query Scripts
 * @package Michaels\Spider\Queries
 */
interface QueryInterface
{
    /**
     * Returns the current Query Script
     * @return string
     */
    public function getScript();

    /**
     * Sets the Query Script
     * @return $this
     */
    public function setScript();

    /**
     * Returns the language of the query script (set by implementer)
     * @return mixed
     */
    public function getScriptLanguage();

    /**
     * Sets the query language (eg OrientSQL, Cypher, etc)
     * @return $this
     */
    public function setScriptLanguage();
}
