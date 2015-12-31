<?php
namespace Spider\Test\Scenarios;
use Spider\Commands\Bag;

/**
 * Class DeleteVertexById
 * @package Spider\Test\Unit\Commands\Languages
 */
class UpdateVertexById extends AbstractScenario
{
    protected static $description = 'it updates (U) vertices by ID';
    protected function buildBag(array $options = null)
    {
        $this->ensureHasId($options);

        $bag = new Bag();
        $bag->update = $this->getData();
        $bag->where = [[
            Bag::ELEMENT_ID,
            Bag::COMPARATOR_EQUAL, // convert to constant
            $options['id'],
            Bag::CONJUNCTION_AND // convert to constant
        ]];

        return $bag;
    }
}
