<?php
namespace Spider\Test\Unit\Exceptions;

use Codeception\Specify;
use Spider\Exceptions\FormattingException;

class FormattingExceptionTest extends \PHPUnit_Framework_TestCase
{
    use Specify;

    public function testGettersAndSetter()
    {
        $this->specify("it gets and sets format", function () {
            $exception = new FormattingException("test message");
            $exception->setFormat("test format");

            $this->assertEquals("test message", $exception->getMessage(), "failed to get/set message");
            $this->assertEquals("test format", $exception->getFormat(), "failed to get/set format");
        });
    }
}

