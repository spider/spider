<?php
namespace Michaels\Spider\Commands;

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
     * Returns the language of the sendCommand script (set by implementer)
     * @return mixed
     */
    public function getScriptLanguage();

    /**
     * Sets the sendCommand language (eg OrientSQL, Cypher, etc)
     *
     * @param $language
     *
     * @return $this
     */
    public function setScriptLanguage($language);

    /**
     * Returns the script if object is called as a string
     * @return string
     */
    public function __toString();
}
