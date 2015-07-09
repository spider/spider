<?php
namespace Michaels\Spider\Queries;

/**
 * Contract for Query Scripts
 * @package Michaels\Spider\Queries
 */
interface CommandInterface
{

    /**
     * New Query object with script
     * @param $script
     */
    public function __construct($script = '');

    /**
     * Returns the current Query Script
     * @return string
     */
    public function getScript();

    /**
     * Sets the Query Script
     *
     * @param $script
     *
     * @return $this
     */
    public function setScript($script);

    /**
     * Returns the language of the query script (set by implementer)
     * @return mixed
     */
    public function getScriptLanguage();

    /**
     * Sets the query language (eg OrientSQL, Cypher, etc)
     *
     * @param $language
     *
     * @return $this
     */
    public function setScriptLanguage($language);
}
