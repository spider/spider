<?php
namespace Spider\Test\Scenarios;
use Spider\Commands\Bag;

/**
 * Class DeleteVertexById
 * @package Spider\Test\Unit\Commands\Languages
 *
 * it deletes (D) a vertex by default using id
 */
class DeleteVerticesByConstraints extends AbstractScenario
{
    protected static $description = 'it deletes (D) vertices by default by complex constraints';
    protected function buildBag(array $options = null)
    {
        $bag = new Bag();
        $bag->delete = true;
        $bag->where = array_merge($this->getWheres(), [[
            Bag::ELEMENT_LABEL,
            Bag::COMPARATOR_EQUAL, // convert to constant
            'label',
            Bag::CONJUNCTION_AND // convert to constant
        ]]);
        $bag->limit = 10;

        return $bag;
    }
}
