<?php
namespace Spider\Test\Scenarios;
use Spider\Commands\Bag;

/**
 * Class DeleteVertexById
 * @package Spider\Test\Unit\Commands\Languages
 */
class DeleteVertexByConstraint extends AbstractScenario
{
    protected static $description = 'it deletes (D) a vertex explicitly using a constraint';
    protected function buildBag(array $options = null)
    {
        $bag = new Bag();
        $bag->delete = true;
        $bag->where = [
            [
                Bag::ELEMENT_TYPE,
                Bag::COMPARATOR_EQUAL, // convert to constant
                Bag::ELEMENT_VERTEX,
                Bag::CONJUNCTION_AND // convert to constant
            ],
            [
                'name',
                Bag::COMPARATOR_EQUAL, // convert to constant
                'marko',
                Bag::CONJUNCTION_AND // convert to constant
            ]
        ];
        $bag->limit = 1;

        return $bag;
    }
}
