<?php
namespace Spider\Test\Unit\Drivers;

use Codeception\Specify;
use Spider\Drivers\Response;
use Spider\Test\Stubs\DriverStub as Driver;


/**
 * Tests the command class
 */
class ResponseTest extends \PHPUnit_Framework_TestCase
{
    protected $response;

    public function setUp()
    {
        // Build the driver stub
        $stub = $this   ->getMockBuilder('\Spider\Test\Stubs\DriverStub')
                        ->getMock();

        // Configure the stub.
        $stub->method('formatAsSet')
             ->willReturn('SET');

        $stub->method('formatAsScalar')
             ->willReturn('SCALAR');

        $stub->method('formatAsTree')
             ->willReturn('TREE');

        $stub->method('formatAsPath')
             ->willReturn('PATH');

        $this->response = new Response(['_raw'=> 'RAW', '_driver'=> $stub]);
    }

    public function testGetRaw()
    {
        $this->assertEquals('RAW', $this->response->getRaw(), "Raw format wasn't properly returned");
    }

    public function testGetSet()
    {
        $this->assertEquals('SET', $this->response->getSet(), "Set format wasn't properly returned");
    }

    public function testGetTree()
    {
        $this->assertEquals('TREE', $this->response->getTree(), "Tree format wasn't properly returned");
    }

    public function testGetPath()
    {
        $this->assertEquals('PATH', $this->response->getPath(), "Path format wasn't properly returned");
    }

    public function testGetScalar()
    {
        $this->assertEquals('SCALAR', $this->response->getScalar(), "Scalar format wasn't properly returned");
    }
}
