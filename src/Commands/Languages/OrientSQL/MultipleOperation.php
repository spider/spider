<?php
namespace Spider\Commands\Languages\OrientSQL;
use Spider\Commands\Bag;
use Spider\Commands\Command;

/**
 * Class MultipleOperation
 * @package Spider\Commands\Languages\OrientSQL
 */
class MultipleOperation extends AbstractOrientSqlProcessor
{
    /**
     * @var SqlBatch
     */
    protected $batch;

    public function process(Bag $bag)
    {
        /* Initialize a Multiple Operation Bag */
        $this->init($bag);
        $this->validateBag();
        $this->batch->begin();

        /* Process the bag */

        // Process the Create Bag
        if (!is_null($bag->create)) {
            foreach ($bag->create as $record) {
                switch ($record[Bag::ELEMENT_TYPE]) {
                    case (Bag::ELEMENT_VERTEX):
                        break;

                    case (Bag::ELEMENT_EDGE):
                        $statement  = "CREATE EDGE " . $record[Bag::ELEMENT_LABEL];

                        $statement .= " FROM ";
                        $statement .= "(".$this->processEmbedded($record[Bag::EDGE_OUTV])->getScript().")";
//                        $statement .= " (SELECT FROM V WHERE name = 'peter')"; // Bag

                        $statement .= " TO ";
                        $statement .= "(".$this->processEmbedded($record[Bag::EDGE_INV])->getScript().")";
//                        $statement .= " (SELECT FROM V WHERE name ='josh')"; // Bag

                        $this->batch->addStatement($statement);
                        break;
                }
            }
        }

        // Process the Retrieve Bag

        // Process the Update Bag

        // Process the Delete Bag

        // Clean up
        $this->batch->end();

        /* Build and Return the Command */
        $command = new Command($this->batch->getScript());
        $command->setScriptLanguage('orientSQL');

        return $command;
    }

    /**
     * Initialize the Command Processor
     * @param Bag $bag
     */
    public function init(Bag $bag)
    {
        $this->bag = $bag;
        $this->batch = new SqlBatch();
        $this->script = '';
    }

    public function processEmbedded(Bag $bag)
    {
        return (new CommandProcessor())->process($bag, true);
    }
}
