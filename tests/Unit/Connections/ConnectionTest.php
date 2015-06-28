<?php
namespace Michaels\Spider\Test\Unit\Connections;

use Codeception\Specify;
use Michaels\Spider\Connections\Connection;
use Michaels\Spider\Drivers\GenericDriver;
use Michaels\Spider\Test\Stubs\DriverStub;
use Michaels\Spider\Test\Stubs\SecondDriverStub;

/*
 * Tests Implementation against ConnectionInterface
 */
class ConnectionTest extends \PHPUnit_Framework_TestCase
{
    use Specify;

    public function testGettersAndSetters()
    {
        $this->specify("it gets driver name", function () {
            $connection = new Connection(new DriverStub(), ['one' => 'one']);

            $this->assertEquals('Michaels\Spider\Test\Stubs\DriverStub', $connection->getDriverName(), "fails to return driver class name");
        });

        $this->specify("it gets driver instance", function () {
            $connection = new Connection(new DriverStub(), ['one' => 'one']);

            $this->assertInstanceOf('Michaels\Spider\Test\Stubs\DriverStub', $connection->getDriver(), 'failed to return driver instance');
        });

        $this->specify("it sets driver instance", function () {
            $connection = new Connection(new DriverStub(), ['one' => 'one']);
            $connection->setDriver(new SecondDriverStub());

            $this->assertInstanceOf('Michaels\Spider\Test\Stubs\SecondDriverStub', $connection->getDriver(), 'failed to return new driver instance');
        });

        $this->specify("it gets properties array", function () {
            $connection = new Connection(new DriverStub(), ['one' => 'one']);

            $this->assertEquals(['one' => 'one', 'config' => []], $connection->getProperties(), 'failed to return properties');
        });

        $this->specify("it sets properties array", function () {
            $connection = new Connection(new DriverStub(), ['one' => 'one']);
            $connection->setProperties(['two' => 'two']);

            $this->assertEquals(['two' => 'two'], $connection->getProperties(), 'failed to update properties');
        });

        $this->specify("it gets individual properties", function () {
            $connection = new Connection(new DriverStub(), ['one' => 'one', 'two' => 'two']);

            $one = $connection->get('one');
            $two = $connection->get('two');

            $this->assertEquals('one', $one, "fails to get first item");
            $this->assertEquals('two', $two, "fails to get first item");
        });

        $this->specify("it sets individual properties", function () {
            $connection = new Connection(new DriverStub(), ['one' => 'one']);
            $connection->set('one', 'new-one');
            $connection->set('two', 'two');
            $connection->set('three.four', 'four');

            $this->assertEquals(['one' => 'new-one', 'two' => 'two', 'three' => ['four' => 'four'], 'config' => []], $connection->getProperties(), "failed to set properties");
        });
    }
}
