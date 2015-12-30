<?php
namespace Spider\Commands\Languages;
use Spider\Commands\Bag;

/**
 * Class AbstractProcessor
 * @package Spider\Commands\Languages
 */
abstract class AbstractProcessor implements ProcessorInterface
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
     * @param Bag $bag
     * @return bool
     */
    protected function isSimpleSelect(Bag $bag)
    {
        return empty($bag->create) && empty($bag->update) && !$bag->delete;
    }

    /**
     * A Bag is a simple CREATE if it does not contain any other Bags
     * @param Bag $bag
     * @return bool
     */
    protected function isSimpleCreate(Bag $bag)
    {
        foreach ($bag->create as $single) {
            foreach ($single as $value) {
                if ($value instanceof Bag) {
                    return false;
                }
            }
        }

        return true;
    }
}
