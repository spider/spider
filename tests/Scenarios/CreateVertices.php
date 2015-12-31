<?php
namespace Spider\Test\Scenarios;
use Spider\Commands\Bag;

/**
 * Class DeleteVertexById
 * @package Spider\Test\Unit\Commands\Languages
 */
class CreateVertices extends AbstractScenario
{
    protected static $description = 'it inserts (C) multiple vertices';
    protected function buildBag(array $options = null)
    {
        $bag = new Bag();
        $bag->create = [
            [
                Bag::ELEMENT_TYPE => Bag::ELEMENT_VERTEX,
                Bag::ELEMENT_LABEL => 'person',
                'name' => 'michael'
            ],
            [
                Bag::ELEMENT_TYPE => Bag::ELEMENT_VERTEX,
                Bag::ELEMENT_LABEL => 'target',
                'name' => 'dylan'
            ],
            [
                Bag::ELEMENT_TYPE => Bag::ELEMENT_VERTEX,
                'name' => 'peter'
            ],
        ];

        return $bag;
    }
}
