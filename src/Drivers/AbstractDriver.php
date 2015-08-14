<?php
namespace Spider\Drivers;

use Spider\Base\Collection;

abstract class AbstractDriver extends Collection implements DriverInterface
{
    /**
     * set of possible formats for responses.
     */
    const FORMAT_SET = 10;
    const FORMAT_TREE = 20;
    const FORMAT_PATH = 30;
    const FORMAT_SCALAR = 40;
    const FORMAT_CUSTOM = 50;

    /**
     * @var array The supported languages and their processors
     */
    protected $languages = [];

    /**
     * @var bool whether or not the driver is currently handling an open transaction
     */
    public $inTransaction = false;

    public function __destruct()
    {
        //rollback changes
        if ($this->inTransaction) {
            $this->StopTransaction(false);
        }
        //close driver
        $this->close();
    }

    /**
     * Checks if a language is supported by this driver
     *
     * @param string $language the language identifier, (orientSQL, gremlin, cypher)
     *
     * @return bool
     */
    public function isSupportedLanguage($language)
    {
        foreach($this->languages as $lang => $processor)
        {
            if($lang == $language)
            {
                return true;
            }
        }
        return false;
    }

    /**
     * Get the processor for a given language
     *
     * @param string $language the language identifier, (orientSQL, gremlin, cypher)
     *
     * @return ProcessorInterface
     */
    public function getProcessor($language)
    {
        $class = static::$languages[$language];
        return new $class;
    }
}
