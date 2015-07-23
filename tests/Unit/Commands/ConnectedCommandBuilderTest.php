<?php
namespace Spider\Test\Unit\Commands;

use Codeception\Specify;
use Spider\Commands\Bag;
use Spider\Commands\Builder;
use Spider\Test\Stubs\CommandProcessorStub;
use Spider\Test\Stubs\ConnectionStub;

/**
 * This tests the retrieval mechanisms of the Commands\Builder.
 * (->all()->one()->first()...)
 *
 * This test mocks both the Connection and CommandProcessor so that
 * when calling a retrieval method, the return value should be the command
 * that was sent.
 *
 * The fluent builder aspects are tested in `FluentCommandBuilderTest`
 * @package Spider\Test\Unit\Commands
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
        // For the tests, all executed commands are just sent back as commands
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
                'command' => Bag::COMMAND_RETRIEVE,
                'projections' => [],
                'target' => "v",
                'limit' => false
            ]);

            $this->assertEquals($expected, $actual->getScript(), 'failed to return correct command');
        });
    }
}
