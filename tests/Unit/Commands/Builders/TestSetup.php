<?php
namespace Spider\Test\Unit\Commands\Builders;

use Codeception\Specify;
use Spider\Commands\Bag;
use Spider\Commands\BaseBuilder;
use Spider\Commands\Builder;

/**
 * Class CommandBuilderTestSetup
 * @package Spider\Test\Unit\Commands
 */
class TestSetup extends \PHPUnit_Framework_TestCase
{
    use Specify;

    /**
     * The Builder Itself
     * @var Builder
     */
    protected $builder;

    public function setup()
    {
        $this->builder = new BaseBuilder();
    }

    // To test a JSON representation of the Command passed through a mock processor (for dispatched commands)
    public function buildExpectedCommand(array $properties)
    {
        $expected = (array)new Bag();
        foreach ($properties as $key => $value) {
            $expected[$key] = $value;
        }
        return json_encode($expected);
    }

    // To test the Command Bag
    public function buildExpectedBag(array $properties)
    {
        $expected = new Bag();
        foreach ($properties as $key => $value) {
            $expected->$key = $value;
        }
        return [$expected];
    }

    public function buildExpectedBags(array $bags)
    {
        $expected = [];
        foreach ($bags as $bag) {
            $expected[] = $this->buildExpectedBag($bag)[0];
        }
        return $expected;
    }
}
