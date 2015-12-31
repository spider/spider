<?php
namespace Spider\Test\Scenarios;
use Spider\Commands\Bag;

/**
 * Class DeleteVertexById
 * @package Spider\Test\Unit\Commands\Languages
 */
class DeleteVertexById extends AbstractScenario
{
    protected static $description = 'it deletes (D) a vertex by default using id';

    protected function buildBag(array $options = null)
    {
        $this->ensureHasId($options);

        $bag = new Bag();
        $bag->delete = true;
        $bag->where = [[
            Bag::ELEMENT_ID,
            Bag::COMPARATOR_EQUAL, // convert to constant
            $options['id'],
            Bag::CONJUNCTION_AND // convert to constant
        ]];

        return $bag;
    }
}
