<?php
namespace Spider\Commands\Languages\OrientSQL;
use Spider\Commands\Bag;

/**
 * Class SimpleCreate
 * @package Spider\Commands\Languages\OrientSQL
 */
class Create extends AbstractOrientSqlProcessor
{

    /**
     * Command Processor
     *
     * Receives a Commands\Bag instance and returns a valid
     * Commands\CommandInterface instance with a native command
     * script for whichever driver is specified
     *
     * @param \Spider\Commands\Bag $bag
     * @return array
     */
    public function process(Bag $bag)
    {
        $this->init($bag);
        $this->validateBag();
        $statements = [];

        foreach ($this->bag->create as $record) {
            switch ( (isset($record[Bag::ELEMENT_TYPE]) ? $record[Bag::ELEMENT_TYPE] : Bag::ELEMENT_VERTEX) ) {
                case (Bag::ELEMENT_EDGE):
                    $statements[] = $this->createEdge($record);
                    break;

                case (Bag::ELEMENT_VERTEX):
                    $statements[] = $this->createVertex($record);
                    break;
            }
        }

        return $statements;
    }

    /**
     * Process a COMMAND_CREATE bag
     * @param array $record
     * @return mixed
     * @throws \Exception
     */
    protected function createEdge(array $record)
    {
        /* CREATE VERTEX */
        $script = '';
        $this->startScript("CREATE EDGE", $script);

        /* Users */
        $this->appendClass(null, $record, $script);

        /* FROM out TO in */
        $this->appendOUTV($record, $script);
        $this->appendINV($record, $script);

        /* Content {} */
        $this->appendContent("CONTENT", $record, $script);

        /* Finished */
        return $script;
    }

    /**
     * Process a COMMAND_CREATE bag
     * @param array $record
     * @return mixed
     * @throws \Exception
     */
    protected function createVertex(array $record)
    {
        /* CREATE VERTEX */
        $script = '';
        $this->startScript("CREATE VERTEX", $script);

        /* Users */
        $this->appendClass(null, $record, $script);

        /* Content {} */
        $this->appendContent("CONTENT", $record, $script);

        /* Finished */
        return $script;
    }

    protected function appendOUTV(array $record, &$script)
    {
        $this->addToScript("FROM", $script);

        if ($record[Bag::EDGE_OUTV] instanceof Bag) {
            $this->addToScript(
                "(".$this->processEmbedded($record[Bag::EDGE_OUTV]).")",
                $script
            );
        } else {
            $this->addToScript($record[Bag::EDGE_OUTV], $script);
        }

    }

    protected function appendINV(array $record, &$script)
    {
        $this->addToScript("TO", $script);

        if ($record[Bag::EDGE_INV] instanceof Bag) {
            $this->addToScript(
                "(".$this->processEmbedded($record[Bag::EDGE_INV]).")",
                $script
            );
        } else {
            $this->addToScript($record[Bag::EDGE_INV], $script);
        }

    }

    protected function processEmbedded(Bag $bag)
    {
        return $this->processor->process($bag, true);
    }
}
