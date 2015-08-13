<?php
namespace Spider\Test\Unit\Connections;

use Codeception\Specify;
use Spider\Connections\Connection;
use Spider\Test\Stubs\DriverStub as Driver;
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

            $connection = new Connection(new Driver(), ['one' => 'one']);

            $this->assertEquals(
                'Spider\Test\Stubs\DriverStub',
                $connection->getDriverName(),
                "fails to return driver class name"
            );
        });

        $this->specify("it gets driver instance", function () {
            $connection = new Connection(new Driver(), ['one' => 'one']);

            $this->assertInstanceOf(
                'Spider\Test\Stubs\DriverStub',
                $connection->getDriver(),
                'failed to return driver instance'
            );
        });

        $this->specify("it sets driver instance", function () {

            $connection = new Connection(new Driver(['identifier'=>'one']), ['one' => 'one']);
            $connection->setDriver(new Driver(['identifier'=>'two']));

            $this->assertEquals(
                'two',
                $connection->getDriver()->identifier,
                'failed to return new driver instance'
            );
        });

        $this->specify("it gets properties array", function () {
            $connection = new Connection(new Driver(), ['one' => 'one']);
            $expected = ['one' => 'one'];

            $this->assertEquals($expected, $connection->getAll(), 'failed to return properties');
        });

        $this->specify("it sets properties array", function () {
            $connection = new Connection(new Driver(), ['one' => 'one']);
            $connection->reset(['two' => 'two']);

            $this->assertEquals(['two' => 'two'], $connection->getAll(), 'failed to update properties');
        });

        $this->specify("it gets individual properties", function () {
            $connection = new Connection(new Driver(), ['one' => 'one', 'two' => 'two', 'a' => 'a']);

            $one = $connection->get('one');
            $two = $connection->get('two');
            $three = $connection->get('a');

            $this->assertEquals('one', $one, "fails to get first credential");
            $this->assertEquals('two', $two, "fails to get second credential");
            $this->assertEquals('a', $three, "fails to get first config");
        });

        $this->specify("it sets individual properties", function () {
            $connection = new Connection(new Driver(), ['one' => 'one']);
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

    public function testInitializeThroughConfigArray()
    {
        $this->specify("it creates a driver from a config array: full class", function () {
            $connection = new Connection([
                'driver' => 'Spider\Test\Stubs\DriverStub',
                'hostname' => 'localhost',
                'port' => 2424,
            ]);

            $this->assertInstanceOf(
                'Spider\Test\Stubs\DriverStub',
                $connection->getDriver(),
                "failed to set correct driver"
            );

            $this->assertEquals('localhost', $connection->get('hostname'), 'failed to set hostname');
            $this->assertEquals(2424, $connection->get('port'), 'failed to set port');
        });

        $this->specify("it creates a driver from a config array: alias", function () {
            $connection = new Connection([
                'driver' => 'orientdb',
                'hostname' => 'localhost',
                'port' => 2424,
            ]);

            $this->assertInstanceOf(
                'Spider\Drivers\OrientDB\Driver',
                $connection->getDriver(),
                "failed to set correct driver"
            );

            $this->assertEquals('localhost', $connection->get('hostname'), 'failed to set hostname');
            $this->assertEquals(2424, $connection->get('port'), 'failed to set port');
        });
    }
}
