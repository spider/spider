<?php
namespace Spider\Test\Scenarios;
use Spider\Commands\Bag;

/**
 * Class DeleteVertexById
 * @package Spider\Test\Unit\Commands\Languages
 */
class CreateVerticesAndupdate extends AbstractScenario
{
    protected static $description ='it creates (C) vertices and updates (U) them with data';
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
                Bag::ELEMENT_LABEL => 'person',
                'name' => 'dylan'
            ],
        ];
        $bag->update = $this->getData();
        $bag->where = [
            [
                Bag::ELEMENT_LABEL,
                Bag::COMPARATOR_EQUAL, // convert to constant
                'person',
                Bag::CONJUNCTION_AND // convert to constant
            ],
            [
                "name",
                Bag::COMPARATOR_EQUAL, // convert to constant
                'michael',
                Bag::CONJUNCTION_AND // convert to constant
            ],
        ];
        $bag->limit = 15;

        return $bag;
    }
}
