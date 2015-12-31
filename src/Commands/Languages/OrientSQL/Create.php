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
    public function createEdge(array $record)
    {
        /* CREATE VERTEX */
        $script = '';
        $this->startScript("CREATE EDGE", $script);

        /* Users */
        $this->appendClass($record, $script);

        /* FROM out TO in */
        $this->appendOUTV($record, $script);
        $this->appendINV($record, $script);

        /* Content {} */
        $this->appendContent($record, $script);

        /* Finished */
        return $script;
    }

    /**
     * Process a COMMAND_CREATE bag
     * @param array $record
     * @return mixed
     * @throws \Exception
     */
    public function createVertex(array $record)
    {
        /* CREATE VERTEX */
        $script = '';
        $this->startScript("CREATE VERTEX", $script);

        /* Users */
        $this->appendClass($record, $script);

        /* Content {} */
        $this->appendContent($record, $script);

        /* Finished */
        return $script;
    }

    public function appendContent(array $record, &$script)
    {
        $data = $record;
        unset($data[Bag::ELEMENT_TYPE]);
        unset($data[Bag::ELEMENT_LABEL]);
        unset($data[Bag::EDGE_OUTV]);
        unset($data[Bag::EDGE_INV]);

        if (!empty($data)) {
            $this->addToScript("CONTENT", $script);
            $this->addToScript(json_encode($data), $script);
        }
    }

    public function appendOUTV(array $record, &$script)
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

    public function appendINV(array $record, &$script)
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

    public function appendClass(array $record, &$script)
    {
        if (isset($record[Bag::ELEMENT_LABEL])) {
            $this->addToScript($record[Bag::ELEMENT_LABEL], $script);
        }
    }

    public function processEmbedded(Bag $bag)
    {
        return $this->processor->process($bag, true);
    }
}
