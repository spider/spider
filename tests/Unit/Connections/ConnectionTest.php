<?php
namespace Michaels\Spider\Test\Unit\Connections;

use Codeception\Specify;
use Michaels\Spider\Connections\Connection;
use Michaels\Spider\Drivers\GenericDriver;
use Michaels\Spider\Test\Stubs\DriverStub;

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
            $connection->setDriver(new GenericDriver());

            $this->assertInstanceOf('Michaels\Spider\Drivers\GenericDriver', $connection->getDriver(), 'failed to return new driver instance');
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

    public function testMapReturnToObject()
    {
        $this->specify("it returns `Graph` by default", function () {
            $connection = new Connection(new DriverStub(), []);
            $response = $connection->getVertex(0); // Returns dummy Native Object

            $this->assertInstanceOf('\Michaels\Spider\Graphs\Graph', $response, 'failed to return a Graph by default');
        });

        $this->specify("it returns `Graph` using `graph`", function () {
            $connection = new Connection(new DriverStub(), [], ['return-object' => 'graph']);
            $response = $connection->getVertex(0); // Returns dummy Native Object

            $this->assertInstanceOf('\Michaels\Spider\Graphs\Graph', $response, 'failed to return a Graph by default');
        });

        $this->specify("it returns native object", function () {
            $connection = new Connection(new DriverStub(), [], ['return-object' => 'native']);
            $response = $connection->getVertex(0); // Returns dummy Native Object

            $this->assertNotInstanceOf('\Michaels\Spider\Test\Stubs\SpecificReturnStub', $response, 'returned SpecificReturnStub');
            $this->assertNotInstanceOf('\Michaels\Spider\Test\Stubs\SpecificReturnMapMethodStub', $response, 'returned SpecificReturnMapMethodStub');
            $this->assertNotInstanceOf('\Michaels\Spider\Graphs\Graph', $response, 'returned Graph');

            $this->assertInstanceOf('\Michaels\Spider\Test\Stubs\NativeReturnStub', $response, 'failed to return the native response');
        });

        $this->specify("it returns specified object using construct", function () {
            $connection = new Connection(new DriverStub(), [], ['return-object' => '\Michaels\Spider\Test\Stubs\SpecificReturnStub']);
            $response = $connection->getVertex(0);

            $this->assertInstanceOf('\Michaels\Spider\Test\Stubs\SpecificReturnStub', $response, 'failed to return a specific object mapped using construct');
        });

        $this->specify("it returns specified object using custom `map` method", function () {
            $connection = new Connection(new DriverStub(), [], ['return-object' => '\Michaels\Spider\Test\Stubs\SpecificReturnMapMethodStub', 'map-method' => 'map']);
            $response = $connection->getVertex(0);

            $this->assertInstanceOf('\Michaels\Spider\Test\Stubs\SpecificReturnMapMethodStub', $response, 'failed to return a specific object mapped using custom `map` method');
        });
    }
}
