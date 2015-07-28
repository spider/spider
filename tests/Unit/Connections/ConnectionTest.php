<?php
namespace Spider\Test\Unit\Connections;

use Codeception\Specify;
use Spider\Connections\Connection;
use Spider\Test\Stubs\FirstDriverStub\Driver as FirstDriver;
use Spider\Test\Stubs\SecondDriverStub\Driver as SecondDriver;

/*
 * Tests Implementation against ConnectionInterface
 */
class ConnectionTest extends \PHPUnit_Framework_TestCase
{
    use Specify;

    public function testGettersAndSetters()
    {
        $this->specify("it gets driver name", function () {
            $connection = new Connection(new FirstDriver(), ['one' => 'one']);

            $this->assertEquals('Spider\Test\Stubs\FirstDriverStub\Driver', $connection->getDriverName(), "fails to return driver class name");
        });

        $this->specify("it gets driver instance", function () {
            $connection = new Connection(new FirstDriver(), ['one' => 'one']);

            $this->assertInstanceOf('Spider\Test\Stubs\FirstDriverStub\Driver', $connection->getDriver(), 'failed to return driver instance');
        });

        $this->specify("it sets driver instance", function () {
            $connection = new Connection(new FirstDriver(), ['one' => 'one']);
            $connection->setDriver(new SecondDriver());

            $this->assertInstanceOf('Spider\Test\Stubs\SecondDriverStub\Driver', $connection->getDriver(), 'failed to return new driver instance');
        });

        $this->specify("it gets properties array", function () {
            $connection = new Connection(new FirstDriver(), ['one' => 'one']);
            $expected = ['one' => 'one'];

            $this->assertEquals($expected, $connection->getAll(), 'failed to return properties');
        });

        $this->specify("it sets properties array", function () {
            $connection = new Connection(new FirstDriver(), ['one' => 'one']);
            $connection->reset(['two' => 'two']);

            $this->assertEquals(['two' => 'two'], $connection->getAll(), 'failed to update properties');
        });

        $this->specify("it gets individual properties", function () {
            $connection = new Connection(new FirstDriver(), ['one' => 'one', 'two' => 'two', 'a' => 'a']);

            $one = $connection->get('one');
            $two = $connection->get('two');
            $three = $connection->get('a');

            $this->assertEquals('one', $one, "fails to get first credential");
            $this->assertEquals('two', $two, "fails to get second credential");
            $this->assertEquals('a', $three, "fails to get first config");
        });

        $this->specify("it sets individual properties", function () {
            $connection = new Connection(new FirstDriver(), ['one' => 'one']);
            $connection->set('one', 'new-one');
            $connection->set('two', 'two');
            $connection->set('three.four', 'four');

            $expected = [
                'one' => 'new-one',
                'two' => 'two',
                'three' => ['four' => 'four'],
            ];

            $this->assertEquals($expected, $connection->getAll(), "failed to set properties");
        });
    }

    public function testPassesCredsToDriverOnOpen()
    {
        $this->specify("it passes credentials and configs to driver", function () {
            $expected = [
                'port' => 1234,
                'hostname' => 'host',
            ];

            $connection = new Connection(new SecondDriver(), $expected);
            $passedToDriver = $connection->open();

            $this->assertArraySubset($expected, $passedToDriver, "failed to pass creds and config to driver");
        });
    }
}
