<?php
namespace Spider\Test\Scenarios;
use Spider\Commands\Bag;

/**
 * Class DeleteVertexById
 * @package Spider\Test\Unit\Commands\Languages
 *
 * it deletes (D) a vertex by default using id
 */
class RetrieveExistingVerticesCreateEdgeUpdateEdge extends AbstractScenario
{
    protected function buildBag(array $options = null)
    {
        $bag = new Bag();
        $bag->create = [
            [
                Bag::ELEMENT_TYPE => Bag::ELEMENT_EDGE,
                Bag::ELEMENT_LABEL => 'knows',
                Bag::EDGE_INV => new Bag([
                    'retrieve' => [],
                    'where' => [
                        ['name', Bag::COMPARATOR_EQUAL, "peter", Bag::CONJUNCTION_AND],
                    ],
                ]),
                Bag::EDGE_OUTV => new Bag([
                    'retrieve' => [],
                    'where' => [
                        ['name', Bag::COMPARATOR_EQUAL, "josh", Bag::CONJUNCTION_AND],
                    ],
                ]),
            ],
        ];
        $bag->update = $this->getData();
        $bag->where = [
            [
                Bag::ELEMENT_ID,
                Bag::COMPARATOR_EQUAL, // convert to constant
                Bag::CREATED_ENTITIES,
                Bag::CONJUNCTION_AND // convert to constant
            ]
        ];

        return $bag;
    }
}
