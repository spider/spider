<?php
namespace Michaels\Spider\Test\Unit\Drivers;

use Codeception\Specify;
use Michaels\Spider\Test\Stubs\AbstractDriverStub\Driver;

class AbstractDriverTest extends \PHPUnit_Framework_TestCase
{
    use Specify;

    public $config;

    public function setup()
    {
        $this->config = [
            'port' => 1234,
            'hostname' => 'hostname'
        ];
    }

    public function testConfiguration()
    {
        $this->specify("it populates configuration credentials from constructor", function () {
            $driver = new Driver($this->config);

            $this->assertEquals(1234, $driver->getCredential('port'), "failed to get correct port");
            $this->assertEquals('hostname', $driver->getCredential('hostname'), "failed to get correct hostname");
        });

        $this->specify("it populates configuration credentials from setCredentials", function () {
            $driver = new Driver();
            $driver->setCredentials($this->config);

            $this->assertEquals(1234, $driver->getCredential('port'), "failed to get correct port");
            $this->assertEquals('hostname', $driver->getCredential('hostname'), "failed to get correct hostname");
        });

        $this->specify("it relies on a default credential", function () {
            $driver = new Driver(['port' => 1235]);

            $this->assertEquals(1235, $driver->getCredential('port'), "failed to get correct port");
            $this->assertEquals('default', $driver->getCredential('hostname'), "failed to get correct hostname");
        });
    }
}

