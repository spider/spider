<?php
namespace Spider\Test\Integration;

use Codeception\Specify;
use Michaels\Manager\IocManager;
use Spider\Integrations\Events\Dispatcher;
use Spider\Spider;
use Spider\Test\Fixtures\OrientFixture;

class SpiderTest extends \PHPUnit_Framework_TestCase
{
    use Specify;

    protected $integrations;
    protected $fullConfig;
    protected $connections;
    protected $options;

    public function setup()
    {
        $this->connections = [
            'default' => 'orient',
            'orient' => [
                'driver' => 'Spider\Drivers\OrientDB\Driver',
                'hostname' => getenv('ORIENTDB_HOSTNAME'),
                'port' => getenv('ORIENTDB_PORT'),
                'username' => getenv('ORIENTDB_USERNAME'),
                'password' => getenv('ORIENTDB_PASSWORD'),
                'database' => 'modern_graph'
            ],
            'neo' => [
                'driver' => 'neo4j',
                'hostname' => getenv('NEO4J_HOSTNAME'),
                'port' => getenv('NEO4J_PORT'),
                'username' => getenv('NEO4J_USERNAME'),
                'password' => getenv('NEO4J_PASSWORD'),
            ]
        ];

        $this->integrations = [
            'events' => 'Spider\Integrations\Events\Dispatcher',
        ];

        $this->options = [
            'errors' => [
                'not_supported' => 'quiet',
            ],
        ];

        $this->fullConfig['connections'] = $this->connections;
        $this->fullConfig['integrations'] = $this->integrations;
        $this->fullConfig = array_merge($this->options, $this->fullConfig);
    }

    public function testConfigure()
    {
        $this->specify("it globally sets up via static `setup`", function () {
            Spider::setup($this->fullConfig);
            $actual = Spider::getSetup();

            $this->assertEquals($this->fullConfig, $actual, "failed to setup global configuration");
        });

        $this->specify("it sets default configuration", function () {
            $spider = new Spider();
            $actual = $spider->getDefaults();

            $this->assertEquals(Spider::getDefaults(), $actual, "failed to set defaults");
        });

        $this->specify("it configures an instance via constructor", function () {
            $spider = new Spider($this->fullConfig);
            $actual = $spider->getConfig();

            $this->assertEquals($this->fullConfig, $actual, "failed to setup global configuration");
        });

        /* ToDo: does this complicate things? */
        $this->specify("it configures an instance via `configure`", function () {
            $spider = new Spider();
            $spider->configure($this->fullConfig);
            $actual = $spider->getConfig();

            $this->assertEquals($this->fullConfig, $actual, "failed to setup global configuration");
        });

        $this->specify("it merges with default configuration", function () {
            $config = [
                'errors' => [
                    'not_supported' => 'fail'
                ]
            ];
            $config['connections'] = $this->fullConfig['connections'];

            $spider = new Spider($config);
            $actual = $spider->getConfig();

            $expected = Spider::getDefaults();
            $expected['connections'] = $config['connections'];
            $expected['errors']['not_supported'] = 'fail';

            $this->assertEquals($expected, $actual, "failed to set defaults");
        });
    }

    public function testInstantiation()
    {
        $this->specify("it creates from static factory: default connection", function () {
            Spider::setup($this->fullConfig);
            $spider = Spider::make();

            $this->assertInstanceOf('Spider\Spider', $spider, "failed to return a Spider");
            $this->assertInstanceOf('Spider\Commands\Query', $spider, "failed to return a Query Builder");
            $this->assertInstanceOf('Spider\Connections\ConnectionInterface', $spider->getConnection(), "invalid connection");
            $this->assertInstanceOf('Spider\Drivers\OrientDB\Driver', $spider->getDriver(), "failed to set driver");
            $this->assertEquals($this->fullConfig, $spider->getConfig(), "failed to setup configuration");
        });

        $this->specify("it creates from static factory: specific connection", function () {
            Spider::setup($this->fullConfig);
            $spider = Spider::make('neo');

            $this->assertInstanceOf('Spider\Spider', $spider, "failed to return a Spider");
            $this->assertInstanceOf('Spider\Commands\Query', $spider, "failed to return a Query Builder");
            $this->assertInstanceOf('Spider\Connections\ConnectionInterface', $spider->getConnection(), "invalid connection");
            $this->assertInstanceOf('Spider\Drivers\Neo4J\Driver', $spider->getDriver(), "failed to set driver");
            $this->assertEquals($this->fullConfig, $spider->getConfig(), "failed to setup configuration");
        });

        $this->specify("it instantiates a new instance with configuration", function () {
            $spider = new Spider($this->fullConfig);
            $actual = $spider->getConfig();

            $this->assertEquals($this->fullConfig, $actual, "failed to setup global configuration");
        });
    }

//     This only tests that Spider sets up Query correctly.
//     Query methods are tested elsewhere
    public function testBasicQueryBuilder()
    {
        $fixture = new OrientFixture();
        $fixture->unload();
        $fixture->load();

        Spider::setup($this->fullConfig);
        $spider = Spider::make(); // orientdb by default

        $response = $spider->select()->getAll();

        $this->assertTrue(is_array($response), "failed to return an array");
        $this->assertCount(6, $response, "failed to return six records");
        $this->assertInstanceOf('Spider\Base\Collection', $response[0], "failed to return collections");

        $fixture->unload();
    }

    public function testFactoryBuilding()
    {
        $this->specify("it builds a new default connection", function () {
            Spider::setup($this->fullConfig);
            $spider = Spider::make();

            $connection = $spider->connection();

            $this->assertInstanceOf('Spider\Connections\ConnectionInterface', $connection, "failed to return a connection");
            $this->assertEquals('Spider\Drivers\OrientDB\Driver', $connection->getDriverName(), "failed to return the correct connection");
        });

        $this->specify("it builds a new specific connection", function () {
            Spider::setup($this->fullConfig);
            $spider = Spider::make();

            $connection = $spider->connection('neo');

            $this->assertInstanceOf('Spider\Connections\ConnectionInterface', $connection, "failed to return a connection");
            $this->assertEquals('Spider\Drivers\Neo4J\Driver', $connection->getDriverName(), "failed to return the correct connection");
        });

        $this->specify("it builds a new default query builder", function () {
            Spider::setup($this->fullConfig);
            $spider = Spider::make();

            $query = $spider->querybuilder();

            $this->assertInstanceOf('Spider\Commands\Query', $query, "failed to return a query builder");
            $this->assertEquals('Spider\Drivers\OrientDB\Driver', $query->getConnection()->getDriverName(), "failed to return with correct connection");
        });

        $this->specify("it builds a new specific query builder", function () {
            Spider::setup($this->fullConfig);
            $spider = Spider::make();

            $query = $spider->querybuilder('neo');

            $this->assertInstanceOf('Spider\Commands\Query', $query, "failed to return a query builder");
            $this->assertEquals('Spider\Drivers\Neo4J\Driver', $query->getConnection()->getDriverName(), "failed to return with correct connection");
        });
    }

    public function testPassesNotSupportedConfiguration()
    {
        $this->specify("it passes a 'fatal' configuration", function () {
            $fixture = new OrientFixture();
            $fixture->unload();
            $fixture->load();

            $config = $this->fullConfig;
            $config['errors']['not_supported'] = 'fatal';

            Spider::setup($config);
            $spider = Spider::make();

            /* ToDo: getTree() will not be unsupported forever */
            $spider->select()->from('person')->getTree();

            $fixture->unload();
        }, ['throws' => 'Spider\Exceptions\NotSupportedException']);

        $this->specify("it passes a 'quiet' configuration", function () {
            $fixture = new OrientFixture();
            $fixture->unload();
            $fixture->load();

            $config = $this->fullConfig;
            $config['errors']['not_supported'] = 'quiet';

            Spider::setup($config);
            $spider = Spider::make();

            /* ToDo: getTree() will not be unsupported forever */
            $spider->select()->from('person')->getTree();

            $fixture->unload();

            /* ToDo: Test exception message */
        }, ['throws' => 'PHPUnit_Framework_Error_Warning']);
    }

    public function testIocContainer()
    {
        $this->specify("it uses the default IoC container", function () {
            Spider::setup($this->fullConfig);
            $spider = Spider::make();

            $actual = $spider->getDI();

            $this->assertInstanceOf('Michaels\Manager\IocManager', $actual, "failed to default ioc container");
        });

        $this->specify("it uses a supplied IocManager", function () {
            $container = new IocManager();
            $container->set('testing', 'new-container');

            Spider::setup($this->fullConfig, $container);
            $spider = Spider::make();

            $actual = $spider->getDI();

            $this->assertInstanceOf('Michaels\Manager\IocManager', $actual, "failed to use supplied ioc container");
            $this->assertEquals('new-container', $container->get('testing'), "failed to return correct container");
        });
    }

    public function testDefiningIntegrations()
    {
        $this->specify("it defines an integrations through various methods", function () {
            $container = new IocManager();
            $container->di('container', new \stdClass());

            Spider::setup([
                'connections' => $this->connections,
                'integrations' => [
                    'string' => 'stdClass',
                    'object' => new \stdClass(),
                    'factory' => function () {
                        return new \stdClass();
                    },
                    'container' => $container,
                ]
            ]);
            $spider = Spider::make();

            $di = $spider->getDI();

            $string = $di->fetch('string');
            $factory = $di->fetch('factory');
            $object = $di->fetch('object');
            $container = $di->fetch('container');

            $this->assertInstanceOf('\stdClass', $string, "Failed to return string factory");
            $this->assertInstanceOf('\stdClass', $factory, "Failed to return string factory");
            $this->assertInstanceOf('\stdClass', $object, "Failed to return string factory");
            $this->assertInstanceOf('\stdClass', $container, "Failed to return string factory");
        });
    }

    public function testDefiningEventDispatcher()
    {
        $this->specify("it uses the default event dispatcher", function () {
            Spider::setup([
                'connections' => $this->connections,
            ]);
            $spider = Spider::make();

            $this->assertInstanceOf('Spider\Integrations\Events\Dispatcher', $spider->getEventDispatcher(), "Failed to return string factory");
        });

        // This doesn't actually test anything, but would require a stub. A better way?
        $this->specify("it uses a defined dispatcher: string", function () {
            Spider::setup([
                'connections' => $this->connections,
                'integrations' => [
                    'events' => 'Spider\Integrations\Events\Dispatcher'
                ]
            ]);
            $spider = Spider::make();

            $this->assertInstanceOf('Spider\Integrations\Events\Dispatcher', $spider->getEventDispatcher(), "Failed to return string factory");
        });

        $this->specify("it uses a defined dispatcher: object", function () {
            $dispatcher = new Dispatcher();
            $dispatcher->testing = 'abcdefg';

            Spider::setup([
                'connections' => $this->connections,
                'integrations' => [
                    'events' =>  $dispatcher
                ]
            ]);
            $spider = Spider::make();

            $this->assertInstanceOf('Spider\Integrations\Events\Dispatcher', $spider->getEventDispatcher(), "Failed to return string factory");
            $this->assertEquals("abcdefg", $dispatcher->testing, "failed to return correct dispatcher");
        });

        $this->specify("it uses a defined dispatcher: factory", function () {
            Spider::setup([
                'connections' => $this->connections,
                'integrations' => [
                    'events' => function () {
                        $dispatcher = new Dispatcher();
                        $dispatcher->testing = 123456;
                        return $dispatcher;
                    }
                ]
            ]);
            $spider = Spider::make();

            $this->assertInstanceOf('Spider\Integrations\Events\Dispatcher', $spider->getEventDispatcher(), "Failed to return string factory");
            $this->assertEquals(123456, $spider->getEventDispatcher()->testing, "failed to return correct dispatcher");
        });

        $this->specify("it uses a defined dispatcher: ioc container", function () {
            $container = new IocManager([
                'events' => 'Spider\Integrations\Events\Dispatcher'
            ]);

            Spider::setup([
                'connections' => $this->connections,
                'integrations' => [
                    'events' => $container
                ]
            ]);
            $spider = Spider::make();

            $this->assertInstanceOf('Spider\Integrations\Events\Dispatcher', $spider->getEventDispatcher(), "Failed to return string factory");
        });
    }

    public function testPassesEventDispatcher()
    {
        $this->specify("it passes the event dispatcher to a Connection through the Connection Manager", function () {
            Spider::setup([
                'connections' => $this->connections,
            ]);
            $spider = Spider::make();

            $connection = $spider->connection();
            $dispatcher = $connection->getDispatcher();

            $this->assertInstanceOf('Spider\Integrations\Events\Dispatcher', $dispatcher, "Failed to return correct event dispatcher");
        });
    }

    public function testAddingAndCallingListeners()
    {
        $this->specify("it adds listeners and emits events", function () {
            $dispatcher = new Dispatcher();
            $events = [];

            $dispatcher->addListener(
                'connections.manager.before_make',
                function () use (&$events) {
                    $events[] = 'fired-before';
                });

            $dispatcher->addListener(
                'connections.manager.after_make',
                function () use (&$events) {
                    $events[] = 'fired-after';
                });

            Spider::setup([
                'connections' => $this->connections,
                'integrations' => [
                    'events' => $dispatcher
                ]
            ]);

            // The ordering here is terrible
            Spider::make();

            $this->assertEquals('fired-before', $events[0], "failed to fire before");
            $this->assertEquals('fired-after', $events[1], 'failed to fire after');
        });

        $this->specify("it allows components to add and dispatch events", function () {
            Spider::setup($this->fullConfig);
            $spider = Spider::make();

            $spider->getEventDispatcher()->addListener(
                'connections.manager.before_make',
                function () use (&$events) {
                    $events[] = 'fired-before_make';
                });

            $spider->getEventDispatcher()->addListener(
                'connections.manager.after_make',
                function () use (&$events) {
                    $events[] = 'fired-after_make';
                });

            $spider->connection();

            $this->assertEquals('fired-before_make', $events[0], "failed to fire before");
            $this->assertEquals('fired-after_make', $events[1], 'failed to fire after');
        });
    }

    public function testExceptions()
    {
        $this->specify("it throws an exception if `make()ing with a non-string alias", function () {
            Spider::setup([
                'connections' => [
                    'orient' => []
                ]
            ]);
            Spider::make([]);
        }, ['throws' => 'InvalidArgumentException']);

        $this->specify("it throws an exception without a default connection", function () {
            Spider::setup([
                'connections' => [
                    'orient' => []
                ]
            ]);
            Spider::make();
        }, ['throws' => 'Spider\Exceptions\ConnectionNotFoundException']);

        $this->specify("it throws an exception without connection configuration", function () {
            Spider::make();
        }, ['throws' => 'Spider\Exceptions\ConnectionNotFoundException']);

        $this->specify("it throws an exception without a valid connection", function () {
            Spider::setup([
                'connections' => [
                    'default' => 'notexistant',
                    'does_exist' => [
                        'driver' => 'nope'
                    ]
                ]
            ]);
            Spider::make();
        }, ['throws' => 'Spider\Exceptions\ConnectionNotFoundException']);
    }
}
