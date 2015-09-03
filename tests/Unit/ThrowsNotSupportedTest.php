<?php
namespace Spider\Test\Unit;

use Codeception\Specify;
use Michaels\Manager\Manager;
use Spider\Test\Stubs\ThrowsNotSupportedStub;

class ThrowsNotSupportedTest extends \PHPUnit_Framework_TestCase
{
    use Specify;

    public function testHandlesBasedOnSpecificConfiguration()
    {
        $this->specify("it throws exception for `fatal`", function () {
            $class = new ThrowsNotSupportedStub(new Manager([
                'errors' => [
                    'not_supported' => 'fatal'
                ]
            ]));

            $class->thisIsNotSupported();
        }, ['throws' => 'Spider\Exceptions\NotSupportedException']);

        $this->specify("it raises a warning for `quiet`", function () {
            $class = new ThrowsNotSupportedStub(new Manager([
                'errors' => [
                    'not_supported' => 'quiet'
                ]
            ]));

            $class->thisIsNotSupported();
        }, ['throws' => 'PHPUnit_Framework_Error_Warning']);

        $this->specify("it does nothing for `silent`", function () {
            $class = new ThrowsNotSupportedStub(new Manager([
                'errors' => [
                    'not_supported' => 'silent'
                ]
            ]));

            $actual = $class->thisIsNotSupported();
            $this->assertTrue($actual, "Failed to be silent");
        });
    }

    public function testHandlesBasedOnOverallConfiguration()
    {
        $this->specify("it throws exception for `fatal`", function () {
            $class = new ThrowsNotSupportedStub(new Manager([
                'errors' => 'fatal'
            ]));

            $class->thisIsNotSupported();
        }, ['throws' => 'Spider\Exceptions\NotSupportedException']);

        $this->specify("it raises a warning for `quiet`", function () {
            $class = new ThrowsNotSupportedStub(new Manager([
                'errors' => 'quiet'
            ]));

            $class->thisIsNotSupported();
        }, ['throws' => 'PHPUnit_Framework_Error_Warning']);

        $this->specify("it does nothing for `silent`", function () {
            $class = new ThrowsNotSupportedStub(new Manager([
                'errors' => 'silent'
            ]));

            $actual = $class->thisIsNotSupported();
            $this->assertTrue($actual, "Failed to be silent");
        });
    }

    public function testHandlesOutlierConfiguration()
    {
        $this->specify("it defaults to `fatal` if no config object", function () {
            $class = new ThrowsNotSupportedStub();

            $class->thisIsNotSupported();
        }, ['throws' => 'Spider\Exceptions\NotSupportedException']);

        $this->specify("it normalizes the configuration", function () {
            $class = new ThrowsNotSupportedStub(new Manager([
                'errors' => 'fAtAl'
            ]));
            $class->thisIsNotSupported();
        }, ['throws' => 'Spider\Exceptions\NotSupportedException']);

        $this->specify("it defaults to `fatal` if config is unrecognized", function () {
            $class = new ThrowsNotSupportedStub(new Manager([
                'errors' => 'unrecognized'
            ]));
            $class->thisIsNotSupported();
        }, ['throws' => 'Spider\Exceptions\NotSupportedException']);
    }
}
