<?php
namespace Spider\Commands\Languages;
use Spider\Commands\Bag;

/**
 * Class AbstractProcessor
 * @package Spider\Commands\Languages
 */
abstract class AbstractProcessor
{
    public function validateBag(Bag $bag = null)
    {
        if ($bag) {
            $bag->validate();
        }

        if (isset($this->bag)) {
           $this->bag->validate();
        }
    }

    /**
     * Is this bag retrieving data
     * @param Bag $bag
     * @return bool
     */
    protected function isSelecting(Bag $bag)
    {
        return (!is_null($bag->retrieve));
    }

    /**
     * Is this bag creating records
     * @param Bag $bag
     * @return bool
     */
    protected function isCreating(Bag $bag)
    {
        return (!empty($bag->create));
    }
}
