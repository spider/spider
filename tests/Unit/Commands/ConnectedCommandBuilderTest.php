<?php
namespace Michaels\Spider\Test\Unit\Commands;

use Codeception\Specify;
use Michaels\Spider\Commands\Bag;
use Michaels\Spider\Commands\Builder;
use Michaels\Spider\Test\Stubs\CommandProcessorStub;
use Michaels\Spider\Test\Stubs\ConnectionStub;

/**
 * This tests the retrieval mechanisms of the builder.
 * (->all()->one()->first()...)
 *
 * The fluent builder aspects are tested in `FluentCommandBuilderTest`
 * @package Michaels\Spider\Test\Unit\Commands
 */
class ConnectedCommandBuilderTest extends \PHPUnit_Framework_TestCase
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

    public function buildExpected(array $properties)
    {
        $expected = (array)new Bag();
        foreach ($properties as $key => $value) {
            $expected[$key] = $value;
        }
        return json_encode($expected);
    }

    /* Begin Tests */
    public function testRetrievalMethods()
    {
        $this->specify("it gets all records", function () {
            $actual = $this->builder
                ->select()
                ->from('v')
                ->all();


            $expected = $this->buildExpected([
                'command' => 'select',
                'projections' => [],
                'from' => "v",
                'limit' => false // Start here -- is this how I want to handle limits?
            ]);

            $this->assertEquals($expected, $actual->getScript(), 'failed to return correct command');
        });
    }
}

