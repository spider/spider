<?php
namespace Spider\Drivers;

use Spider\Base\Collection;
use Spider\Commands\BaseBuilder;
use Spider\Commands\Command;
use Spider\Commands\Languages\ProcessorInterface;
use Spider\Exceptions\NotSupportedException;
use Symfony\Component\Config\Definition\Exception\Exception;

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
        if (isset($this->languages[$language])) {
            return true;
        }
        return false;
    }

    /**
     * Get the processor for a given language
     *
     * @param string $language the language identifier, (orientSQL, gremlin, cypher)
     * @return ProcessorInterface
     * @throws NotSupportedException
     */
    public function getProcessor($language)
    {
        if (!$this->isSupportedLanguage($language)) {
            throw new NotSupportedException("$language does not have a supported languabe processor");
        }

        $class = $this->languages[$language];
        return new $class;
    }

    /**
     * Transforms a Builder to a Command if needed
     * @param $command
     * @param $language
     * @return Command
     * @throws NotSupportedException
     * @throws \Exception
     */
    protected function ensureCommand($command, $language)
    {
        if ($command instanceof BaseBuilder) {
            $processor = new $this->languages[$language];
            $command = $command->getCommand($processor);
            return $command;
        }

        if (!$command instanceof Command) {
            throw new \Exception("Drivers only accept Commands or instances of BaseBuilder");
        }

        if (!$this->isSupportedLanguage($command->getScriptLanguage())) {
            throw new NotSupportedException(__CLASS__ . " does not support " . $command->getScriptLanguage());
        }

        return $command;
    }
}
