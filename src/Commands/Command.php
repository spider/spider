<?php
namespace Michaels\Spider\Commands;

/**
 * Class Query
 * @package Michaels\Spider\Queries
 */
class Command implements CommandInterface
{
    protected $script;
    protected $language;

    /**
     * New Query object with script
     *
     * @param $script
     */
    public function __construct($script = '')
    {
        $this->setScript($script);
    }

    /**
     * Returns the current Query Script
     * @return string
     */
    public function getScript()
    {
        return $this->script;
    }

    /**
     * Sets the Query Script
     *
     * @param $script
     *
     * @return $this
     */
    public function setScript($script)
    {
        $this->script = $script;
    }

    /**
     * Returns the language of the query script (set by implementer)
     * @return mixed
     */
    public function getScriptLanguage()
    {
        return $this->language;
    }

    /**
     * Sets the query language (eg OrientSQL, Cypher, etc)
     *
     * @param $language
     *
     * @return $this
     */
    public function setScriptLanguage($language)
    {
        $this->language = $language;
    }

    /**
     * Returns the script if object is called as a string
     * @return string
     */
    public function __toString()
    {
        return $this->getScript();
    }
}
