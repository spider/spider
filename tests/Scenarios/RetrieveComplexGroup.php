<?php
namespace Spider\Test\Scenarios;
use Spider\Commands\Bag;

/**
 * Class DeleteVertexById
 * @package Spider\Test\Unit\Commands\Languages
 */
class RetrieveComplexGroup extends AbstractScenario
{
    protected static $description ='(R) by label, type, and wheres - groups and limits';
    protected function buildBag(array $options = null)
    {
        $bag = new Bag([
            'retrieve' => [],
        ]);

        $bag->where = array_merge(static::getWheres(), [
            [
                Bag::ELEMENT_LABEL,
                Bag::COMPARATOR_EQUAL, // convert to constant
                'target',
                Bag::CONJUNCTION_AND // convert to constant
            ],
            [Bag::ELEMENT_TYPE, Bag::COMPARATOR_EQUAL, Bag::ELEMENT_VERTEX, Bag::CONJUNCTION_AND],
        ]);
        $bag->limit = 3;
        $bag->groupBy = ['field1'];

        return $bag;
    }
}
