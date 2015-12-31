<?php
namespace Spider\Test\Scenarios;
use Spider\Commands\Bag;

/**
 * Class DeleteVertexById
 * @package Spider\Test\Unit\Commands\Languages
 */
class UpdateVerticesByConstraints extends AbstractScenario
{
    protected static $description ='it updates (U) vertices by complex constraints';
    protected function buildBag(array $options = null)
    {
        $bag = new Bag();
        $bag->update = $this->getData();
        $bag->where = array_merge($this->getWheres(), [[
            Bag::ELEMENT_LABEL,
            Bag::COMPARATOR_EQUAL, // convert to constant
            'target',
            Bag::CONJUNCTION_AND // convert to constant
        ]]);
        $bag->limit = 10;

        return $bag;
    }
}
