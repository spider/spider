<?php
namespace Spider\Test\Scenarios;
use Spider\Commands\Bag;

/**
 * Class DeleteVertexById
 * @package Spider\Test\Unit\Commands\Languages
 *
 * it deletes (D) a vertex by default using id
 */
class RetrieveEdgeByLabelAndSingleConstraint extends AbstractScenario
{
    protected static $description = '(R) EDGE by label, type, and single where';
    protected function buildBag(array $options = null)
    {
        $bag = new Bag([
            'retrieve' => [],
            'where' => [
                [Bag::ELEMENT_TYPE, Bag::COMPARATOR_EQUAL, Bag::ELEMENT_EDGE, Bag::CONJUNCTION_AND],
                [
                    Bag::ELEMENT_LABEL,
                    Bag::COMPARATOR_EQUAL, // convert to constant
                    'target',
                    Bag::CONJUNCTION_AND // convert to constant
                ]
            ],
        ]);

        return $bag;
    }
}
