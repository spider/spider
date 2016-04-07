<?php
namespace Spider\Test\Scenarios;
use Spider\Commands\Bag;

/**
 * Class DeleteVertexById
 * @package Spider\Test\Unit\Commands\Languages
 */
class CreateVerticesAndEdge extends AbstractScenario
{
    protected static $description ='it creates (C) two vertices and creates (C) an edge in between (R)';
    protected function buildBag(array $options = null)
    {
        $bag = new Bag([
            'create' => [
                [
                    Bag::ELEMENT_TYPE => Bag::ELEMENT_VERTEX,
                    Bag::ELEMENT_LABEL => 'person',
                    'name' => 'michael'
                ],
                [
                    Bag::ELEMENT_TYPE => Bag::ELEMENT_VERTEX,
                    Bag::ELEMENT_LABEL => 'person',
                    'name' => 'dylan'
                ],
                [
                    Bag::ELEMENT_TYPE => Bag::ELEMENT_EDGE,
                    Bag::ELEMENT_LABEL => 'knows',
                    Bag::EDGE_INV => new Bag([
                        'retrieve' => [],
                        'where' => [
                            ['name', Bag::COMPARATOR_EQUAL, "michael", Bag::CONJUNCTION_AND],
                        ],
                    ]),
                    Bag::EDGE_OUTV => new Bag([
                        'retrieve' => [],
                        'where' => [
                            ['name', Bag::COMPARATOR_EQUAL, "dylan", Bag::CONJUNCTION_AND],
                        ],
                    ]),
                ],
            ]
        ]);

        return $bag;
    }
}
