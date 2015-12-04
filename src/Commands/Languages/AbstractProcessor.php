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
}
