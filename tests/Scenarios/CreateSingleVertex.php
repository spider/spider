<?php
namespace Spider\Test\Scenarios;
use Spider\Commands\Bag;

/**
 * Class DeleteVertexById
 * @package Spider\Test\Unit\Commands\Languages
 */
class CreateSingleVertex extends AbstractScenario
{
    protected static $description = 'it inserts (C) a single vertex';
    protected function buildBag(array $options = null)
    {
        $bag = new Bag();
        $bag->create = [[
            Bag::ELEMENT_TYPE => Bag::ELEMENT_VERTEX,
            Bag::ELEMENT_LABEL => 'person',
            'name' => 'michael'
        ]];

        return $bag;
    }
}
