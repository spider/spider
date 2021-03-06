<?php
namespace Spider\Commands;

/**
 * Command: Contains the command script to be executed by the driver
 *
 * For instance, a Command object may contain the OrientDB query
 *      "SELECT FROM users WHERE username = 'michael'"
 *
 * You may create a Command explicitly or use the Command Builder and
 * a driver specific Command Processor.
 */
interface CommandInterface
{
    /**
     * Returns the current Command Script
     * @return string
     */
    public function getScript();

    /**
     * Sets the Command Script
     * @param $script
     * @return $this
     */
    public function setScript($script);

    /**
     * Returns the language of the current command script script
     * @return mixed
     */
    public function getScriptLanguage();

    /**
     * Sets the current script language (eg orientSQL, Cypher, etc)
     * @param $language
     * @return $this
     */
    public function setScriptLanguage($language);
}
