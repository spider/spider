<?php
namespace Spider\Test\Unit\Commands\Builder;

use Codeception\Specify;
use Spider\Commands\Bag;
use Spider\Commands\Builder;
use Spider\Test\Stubs\CommandProcessorStub;
use Spider\Test\Stubs\ConnectionStub;

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
        $this->builder = new Builder(new CommandProcessorStub(), new ConnectionStub());
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
        return $expected;
    }
}
