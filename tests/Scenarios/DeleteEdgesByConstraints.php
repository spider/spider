<?php
namespace Spider\Test\Scenarios;
use Spider\Commands\Bag;

/** @todo: THIS SCENARIO IS NOT IMPLEMENTED ANYWHERE YET */


/**
 * Class DeleteVertexById
 * @package Spider\Test\Unit\Commands\Languages
 *
 * it deletes (D) a vertex by default using id
 */
class DeleteEdgesByConstraints extends AbstractScenario
{
    protected function buildBag(array $options = null)
    {
        $bag = new Bag();
        $bag->delete = true;
        $bag->where = array_merge(static::getWheres(), [
            [
                Bag::ELEMENT_LABEL,
                Bag::COMPARATOR_EQUAL, // convert to constant
                'label',
                Bag::CONJUNCTION_AND // convert to constant
            ],
            [
                Bag::ELEMENT_TYPE,
                Bag::COMPARATOR_EQUAL, // convert to constant
                Bag::ELEMENT_EDGE,
                Bag::CONJUNCTION_AND // convert to constant
            ]
        ]);

        return $bag;
    }
}
