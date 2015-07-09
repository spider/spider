<?php
namespace Michaels\Spider\Test\Unit\Connections;

use Codeception\Specify;
use Michaels\Spider\Connections\Connection;
use Michaels\Spider\Test\Stubs\FirstDriverStub\Driver as FirstDriver;
use Michaels\Spider\Test\Stubs\SecondDriverStub\Driver as SecondDriver;

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

            $this->assertEquals('Michaels\Spider\Test\Stubs\FirstDriverStub\Driver', $connection->getDriverName(), "fails to return driver class name");
        });

        $this->specify("it gets driver instance", function () {
            $connection = new Connection(new FirstDriver(), ['one' => 'one']);

            $this->assertInstanceOf('Michaels\Spider\Test\Stubs\FirstDriverStub\Driver', $connection->getDriver(), 'failed to return driver instance');
        });

        $this->specify("it sets driver instance", function () {
            $connection = new Connection(new FirstDriver(), ['one' => 'one']);
            $connection->setDriver(new SecondDriver());

            $this->assertInstanceOf('Michaels\Spider\Test\Stubs\SecondDriverStub\Driver', $connection->getDriver(), 'failed to return new driver instance');
        });

        $this->specify("it gets properties array", function () {
            $connection = new Connection(new FirstDriver(), ['one' => 'one']);
            $expected = [
                'credentials' => ['one' => 'one'],
                'config' => [],
            ];

            $this->assertEquals($expected, $connection->getProperties(), 'failed to return properties');
        });

        $this->specify("it sets properties array", function () {
            $connection = new Connection(new FirstDriver(), ['one' => 'one']);
            $connection->setProperties(['two' => 'two']);

            $this->assertEquals(['two' => 'two'], $connection->getProperties(), 'failed to update properties');
        });

        $this->specify("it gets individual properties", function () {
            $connection = new Connection(new FirstDriver(), ['one' => 'one', 'two' => 'two'], ['a' => 'a']);

            $one = $connection->get('credentials.one');
            $two = $connection->get('credentials.two');
            $three = $connection->get('config.a');

            $this->assertEquals('one', $one, "fails to get first credential");
            $this->assertEquals('two', $two, "fails to get second credential");
            $this->assertEquals('a', $three, "fails to get first config");
        });

        $this->specify("it sets individual properties", function () {
            $connection = new Connection(new FirstDriver(), ['one' => 'one']);
            $connection->set('credentials.one', 'new-one');
            $connection->set('credentials.two', 'two');
            $connection->set('config.three.four', 'four');

            $expected = [
                'credentials' => ['one' => 'new-one', 'two' => 'two'],
                'config' => ['three' => ['four' => 'four']],
            ];

            $this->assertEquals($expected, $connection->getProperties(), "failed to set properties");
        });
    }

    public function testPassesCredsToDriverOnOpen()
    {
        $this->specify("it passes credentials and configs to driver on open", function () {
            $connection = new Connection(new SecondDriver(), ['port' => 1234], ['some-config' => 'set']);
            $passedToDriver = $connection->open();
            $expected = [
                'credentials' => ['port' => 1234],
                'config' => ['some-config' => 'set'],
            ];

            $this->assertEquals($expected, $passedToDriver, "failed to pass creds and config to driver");
        });
    }
}
