<?php
namespace Michaels\Spider\Test\Unit\Connections;

use Codeception\Specify;
use Michaels\Spider\Connections\Manager;

/*
 * Tests Connection Manager. Does not test methods covered in Michaels\Manager
 */
class ManagerTest extends \PHPUnit_Framework_TestCase
{
    use Specify;

    protected $connections;
    protected $config;

    public function setup()
    {
        $this->connections = [
            'default'     => 'default-connection',
            'default-connection' => [
                'driver' => 'Michaels\Spider\Drivers\GenericDriver',
                'username' => 'username',
                'host' => 'host',
                'pass' => 'pass'
            ],
            'connection-one' => [
                'driver' => 'Michaels\Spider\Test\Stubs\DriverStub',
                'credentials' => 'one-credentials',
                'other' => 'one-other'
            ],
            'connection-two' => [
                'driver' => 'Some\Driver\Two',
                'credentials' => 'two-credentials',
                'other' => 'two-other'
            ]
        ];

        $this->config = [
            'something' => 'something-value',
            'return-object' => false, // return native object
//            'return-object' => 'Class\Name\Here',
//            'map-method' => 'map',
        ];
    }

    /* Inherits from Michaels\Manager\Traits\ManagesItemsTrait, which is self-tested */
    public function testMakeStoredConnections()
    {
        $this->specify("it makes a new instance of the default connection", function () {
            $manager = new Manager($this->connections);
            $connection = $manager->make();

            // Connection is a valid instance of Connection
            $this->assertInstanceOf(
                'Michaels\Spider\Connections\ConnectionInterface',
                $connection,
                "failed to return an valid connection"
            );

            // Connection is using the correct driver
            $this->assertEquals(
                $this->connections['default-connection']['driver'],
                $connection->getDriverName(),
                "failed to set correct driver"
            );

            // Connection is using the correct properties
            $expected = $this->connections['default-connection'];
            $expected['config'] = [];
            unset($expected['driver']);

            $this->assertEquals(
                $expected,
                $connection->getProperties(),
                "failed to set correct properties"
            );
        });

        $this->specify("it makes a new instance of a specified connection", function () {
            $manager = new Manager($this->connections);
            $connection = $manager->make('connection-one');

            // Connection is a valid instance of Connection
            $this->assertInstanceOf(
                'Michaels\Spider\Connections\ConnectionInterface',
                $connection,
                "failed to return an valid connection"
            );

            // Connection is using the correct driver
            $this->assertEquals(
                $this->connections['connection-one']['driver'],
                $connection->getDriverName(),
                "failed to set correct driver"
            );

            // Connection is using the correct properties
            $expected = $this->connections['connection-one'];
            unset($expected['driver']);
            $expected['config'] = [];

            $this->assertEquals(
                $expected,
                $connection->getProperties(),
                "failed to set correct properties"
            );
        });

        $this->specify("it sets config correctly", function () {
            $manager = new Manager($this->connections, $this->config);
            $connection = $manager->make('connection-one');

            $this->assertEquals($this->config['something'], $connection->get('config.something'), 'failed to set and return config value');
        });
    }
}
