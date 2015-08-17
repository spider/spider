<?php
namespace Spider\Test\Unit\Drivers;

use Codeception\Specify;
use Spider\Test\Stubs\DriverStub;

class AbstractDriverTest extends \PHPUnit_Framework_TestCase
{
    use Specify;

    public function testIsSupportedLanaguage()
    {
        $this->specify("it check for supported language", function () {
            $driver = new DriverStub();

            $this->assertTrue($driver->isSupportedLanguage('stub'), "failed asserting true");
            $this->assertFalse($driver->isSupportedLanguage('invalid'), "failed asserting false");
        });
    }

    public function testGetProcessor()
    {
        $this->specify("it retrieves the correct processor", function () {
            $driver = new DriverStub();

            $processor = $driver->getProcessor('stub');
            $this->assertInstanceOf('Spider\Test\Stubs\CommandProcessorStub', $processor, "failed to return correct processor");
        });

        $this->specify("it throws an exception if fetching invalid processor", function () {
            $driver = new DriverStub();
            $processor = $driver->getProcessor('invalid');
        }, ['throws' => 'Spider\Exceptions\NotSupportedException']);
    }
}
