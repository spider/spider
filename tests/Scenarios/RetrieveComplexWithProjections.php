<?php
namespace Spider\Test\Scenarios;
use Spider\Commands\Bag;

/**
 * Class DeleteVertexById
 * @package Spider\Test\Unit\Commands\Languages
 *
 * it deletes (D) a vertex by default using id
 */
class RetrieveComplexWithProjections extends AbstractScenario
{
    protected static $description = '(R) projections by label, type, and wheres - orders and limits';
    protected function buildBag(array $options = null)
    {
        $bag = new Bag([
            'retrieve' => ['field1', 'field2'],
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
        $bag->orderBy = [['field1', Bag::ORDER_DESC]];

        return $bag;
    }
}
