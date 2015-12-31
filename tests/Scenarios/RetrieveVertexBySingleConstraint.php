<?php
namespace Spider\Test\Scenarios;
use Spider\Commands\Bag;

/**
 * Class DeleteVertexById
 * @package Spider\Test\Unit\Commands\Languages
 */
class RetrieveVertexBySingleConstraint extends AbstractScenario
{
    protected static $description = '(R) by a single where constraint and no label';
    protected function buildBag(array $options = null)
    {
        $bag = new Bag([
            'retrieve' => [],
            'where' => [
                ['name', Bag::COMPARATOR_EQUAL, "josh", Bag::CONJUNCTION_AND],
            ],
        ]);

        return $bag;
    }
}
