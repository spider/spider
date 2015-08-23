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
class Command implements CommandInterface
{
    /** @var string Native script to be executed */
    protected $script;

    /** @var  string Optional specified script language */
    protected $language;

    /** @var string Is this a read or write command */
    protected $rw = 'write';

    /**
     * Create a new Command from a text string
     * @param string $script
     * @param null $language
     */
    public function __construct($script = '', $language = null)
    {
        $this->setScript($script);
        $this->setScriptLanguage($language);
    }

    /**
     * Returns the current Command Script
     * @return string
     */
    public function getScript()
    {
        return $this->script;
    }

    /**
     * Sets the Command Script
     * @param string $script
     * @return $this
     */
    public function setScript($script)
    {
        $this->script = $script;
    }

    /**
     * Returns the language of the current command script script
     * @return string
     */
    public function getScriptLanguage()
    {
        return $this->language;
    }

    /**
     * Sets the current script language (eg orientSQL, Cypher, etc)
     * @param $language
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
