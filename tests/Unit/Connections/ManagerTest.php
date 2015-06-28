<?php
namespace Michaels\Spider\Test\Unit\Connections;

use Codeception\Specify;
use Michaels\Manager\Exceptions\ItemNotFoundException;
use Michaels\Spider\Connections\ConnectionNotFoundException;
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
                'driver' => 'Michaels\Spider\Test\Stubs\DriverStub',
                'username' => 'username',
                'host' => 'host',
                'pass' => 'pass'
            ],
            'connection-one' => [
                'driver' => 'Michaels\Spider\Test\Stubs\SecondDriverStub',
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

        $this->specify("it throws an exception if making a default connection that was not registered", function () {
            $manager = new Manager();
            $manager->make();
        }, ['throws' => new ConnectionNotFoundException()]);

        $this->specify("it throws an exception if making a non-existent connection", function () {
            $manager = new Manager($this->connections);
            $manager->make('doesnotexist');
        }, ['throws' => new ConnectionNotFoundException()]);

        $this->specify("it sets config correctly", function () {
            $manager = new Manager($this->connections, $this->config);
            $connection = $manager->make('connection-one');

            $this->assertEquals($this->config['something'], $connection->get('config.something'), 'failed to set and return config value');
        });
    }

    public function testCacheConnections()
    {
        $this->specify("it caches connection with make", function () {
            $manager = new Manager($this->connections);

            $defaultConnection = $manager->make();
            $connectionOne = $manager->make('connection-one');

            $expected = [
                'default-connection' => $defaultConnection,
                'connection-one' => $connectionOne,
            ];

            $this->assertEquals($expected, $manager->get('cache'), "failed to cache connections");
        });

        $this->specify("it clears cached connections", function () {
            $manager = new Manager($this->connections);

            $manager->make();
            $manager->make('connection-one');

            $manager->clearCache();

            $expected = [];

            $this->assertEquals($expected, $manager->get('cache'), "failed to clear cache");
        });

        $this->specify("it returns a specific cached connection", function () {
            $manager = new Manager($this->connections);

            $defaultConnection = $manager->make();
            $connectionOne = $manager->make('connection-one');

            $this->assertEquals($defaultConnection, $manager->get('cache.default-connection'), "failed to return default connection");
            $this->assertEquals($connectionOne, $manager->get('cache.connection-one'), "failed to return connection=one");
        });

        $this->specify("it throws an exception if requesting a non cached item", function () {
            $manager = new Manager($this->connections);

            $manager->get('cache.connection-one');
        }, ['throws' => new ItemNotFoundException()]);
    }

    public function testFetchConnections()
    {
        $this->specify("it returns an already cached item via fetch()", function () {
            $manager = new Manager($this->connections);

            $defaultConnection = $manager->make();
            $connectionOne = $manager->make('connection-one');

            $this->assertEquals($connectionOne, $manager->fetch('connection-one'), "failed to return cached connection");
            $this->assertEquals($defaultConnection, $manager->fetch('default-connection'), "failed to return cached default connection by name");
            $this->assertEquals($defaultConnection, $manager->fetch(), "failed to return default cached connection by default");
        });

        $this->specify("it returns a new connection via fetch() and caches", function () {
            $manager = new Manager($this->connections);

            // These are for $expected
            $defaultConnection = $manager->make();
            $connectionOne = $manager->make('connection-one');

            // Now they don't exist
            $manager->clearCache();

            // So Manager will make and cache them
            $this->assertEquals($connectionOne, $manager->fetch('connection-one'), "failed to return a new connection-one");
            $this->assertEquals($connectionOne, $manager->get('cache.connection-one'), 'failed to cache connection-one');

            $this->assertEquals($defaultConnection, $manager->fetch(), "failed to return default cached connection by default");
            $this->assertEquals($defaultConnection, $manager->get('cache.default-connection'), 'failed to cache default-connection');
        });
    }
}
