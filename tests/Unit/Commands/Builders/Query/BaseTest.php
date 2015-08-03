<?php
namespace Spider\Test\Unit\Commands\Builders\Query;

use Codeception\Specify;
use Spider\Commands\Bag;
use Spider\Commands\Command;
use Spider\Graphs\ID as TargetID;

class BaseTest extends TestSetup
{
    use Specify;

    /*
     * Normally, these tests would return formatted Responses that may or may not be
     * instances of Response
     *
     * For testing we get back a Response()
     * Response::getRaw() -> Command() -> getScript() -> json_encode($bag)
     * Response::formattedAsX = true
     */
    public function testRetrievalMethods()
    {
        $this->markTestSkipped('incorrect expectations, needs a rewrite');
        $this->specify("it gets `all` records as a set", function () {
            /* Normally, this would return an array of Collections */
            $actual = $this->builder
                ->select()
                ->from('v')
                ->all();

            $expected = $this->buildExpectedCommand([
                'command' => Bag::COMMAND_RETRIEVE,
                'projections' => [],
                'target' => "v",
                'limit' => false
            ]);

            $this->assertInstanceOf("Spider\\Drivers\\Response", $actual, "failed to return a Response");
            $this->assertTrue($actual->formattedAsSet, 'failed to return correctly formatted response');
            $this->assertEquals($expected, $actual->getRaw()->getScript(), 'failed to return correct command');
        });

        $this->specify("it gets `one` record as a Set (single collection)", function () {
            /* Normally, this would return a single collection */
            $actual = $this->builder
                ->select()
                ->from('v')
                ->one();

            $expected = $this->buildExpectedCommand([
                'command' => Bag::COMMAND_RETRIEVE,
                'projections' => [],
                'target' => "v",
                'limit' => 1
            ]);

            $this->assertInstanceOf("Spider\\Drivers\\Response", $actual, "failed to return a Response");
            $this->assertTrue($actual->formattedAsSet, 'failed to return correctly formatted response');
            $this->assertEquals($expected, $actual->getRaw()->getScript(), 'failed to return correct command');
        });

        $this->specify("it gets `first` record as a Set (single collection)", function () {
            /* Normally, this would return a single collection */
            $actual = $this->builder
                ->select()
                ->from('v')
                ->first();

            $expected = $this->buildExpectedCommand([
                'command' => Bag::COMMAND_RETRIEVE,
                'projections' => [],
                'target' => "v",
                'limit' => 1
            ]);

            $this->assertInstanceOf("Spider\\Drivers\\Response", $actual, "failed to return a Response");
            $this->assertTrue($actual->formattedAsSet, 'failed to return correctly formatted response');
            $this->assertEquals($expected, $actual->getRaw()->getScript(), 'failed to return correct command');
        });

        $this->specify("it gets a `path`", function () {
            /* Normally, this would return a single collection */
            $actual = $this->builder
                ->select()
                ->from('v')
                ->path();

            $expected = $this->buildExpectedCommand([
                'command' => Bag::COMMAND_RETRIEVE,
                'projections' => [],
                'target' => "v",
                'format' => Bag::FORMAT_PATH
            ]);

            $this->assertInstanceOf("Spider\\Drivers\\Response", $actual, "failed to return a Response");
            $this->assertTrue($actual->formattedAsPath, 'failed to return correctly formatted response');
            $this->assertEquals($expected, $actual->getRaw()->getScript(), 'failed to return correct command');
        });

        $this->specify("it gets a `tree`", function () {
            /* Normally, this would return a single collection */
            $actual = $this->builder
                ->select()
                ->from('v')
                ->tree();

            $expected = $this->buildExpectedCommand([
                'command' => Bag::COMMAND_RETRIEVE,
                'projections' => [],
                'target' => "v",
                'format' => Bag::FORMAT_TREE
            ]);

            $this->assertInstanceOf("Spider\\Drivers\\Response", $actual, "failed to return a Response");
            $this->assertTrue($actual->formattedAsTree, 'failed to return correctly formatted response');
            $this->assertEquals($expected, $actual->getRaw()->getScript(), 'failed to return correct command');
        });

        $this->specify("it gets a `scalar`", function () {
            /* Normally, this would return a single collection */
            $actual = $this->builder
                ->select()
                ->from('v')
                ->scalar();

            $expected = $this->buildExpectedCommand([
                'command' => Bag::COMMAND_RETRIEVE,
                'projections' => [],
                'target' => "v",
                'format' => Bag::FORMAT_SCALAR
            ]);

            $this->assertInstanceOf("Spider\\Drivers\\Response", $actual, "failed to return a Response");
            $this->assertTrue($actual->formattedAsScalar, 'failed to return correctly formatted response');
            $this->assertEquals($expected, $actual->getRaw()->getScript(), 'failed to return correct command');
        });

        $this->specify("it gets a `set`", function () {
            /* Normally, this would return a single collection */
            $actual = $this->builder
                ->select()
                ->from('v')
                ->set(); // alias of get()

            $expected = $this->buildExpectedCommand([
                'command' => Bag::COMMAND_RETRIEVE,
                'projections' => [],
                'target' => "v",
            ]);

            $this->assertInstanceOf("Spider\\Drivers\\Response", $actual, "failed to return a Response");
            $this->assertTrue($actual->formattedAsSet, 'failed to return correctly formatted response');
            $this->assertEquals($expected, $actual->getRaw()->getScript(), 'failed to return correct command');
        });

        $this->specify("it executes a `command`", function () {
            $actual = $this->builder
                ->command(new Command("some script"));

            $this->assertInstanceOf(
                "Spider\\Drivers\\Response",
                $actual,
                'failed to return correct command'
            );
            $this->assertEquals('some script', $actual->getRaw()->getScript(), 'failed to return correct script');
        });

        $this->specify("it `dispatch`es a given command", function () {
            $actual = $this->builder
                ->dispatch(new Command("some script"));

            $this->assertInstanceOf(
                "Spider\\Drivers\\Response",
                $actual,
                'failed to return correct command'
            );
            $this->assertEquals('some script', $actual->getRaw()->getScript(), 'failed to return correct script');
        });

        $this->specify("it `dispatch`es the current command", function () {
            $actual = $this->builder
                ->select('single')
                ->from('v')
                ->dispatch();

            $expected = $this->buildExpectedCommand([
                'command' => Bag::COMMAND_RETRIEVE,
                'projections' => ['single'],
                'target' => "v",
            ]);

            $this->assertInstanceOf(
                "Spider\\Drivers\\Response",
                $actual,
                'failed to return correct command'
            );
            $this->assertEquals($expected, $actual->getRaw()->getScript(), 'failed to return correct script');
        });
    }

    /* ToDo: fromDb tests, an experimental feature */
}
