<?php
namespace Spider\Test\Scenarios;
use Spider\Commands\Bag;

/**
 * Class DeleteVertexById
 * @package Spider\Test\Unit\Commands\Languages
 */
class RetrieveTwoVerticesAndCreateEdge extends AbstractScenario
{
    protected static $description = 'finds (R) two vertices and creates (C) an edge in between';
    protected function buildBag(array $options = null)
    {
        $bag = new Bag([
            'create' => [
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
            ]
        ]);

        return $bag;
    }
}
