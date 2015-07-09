<?php
namespace Michaels\Spider\Test\Unit\Drivers;

use Codeception\Specify;

class OrientCommandBuilderTest extends \PHPUnit_Framework_TestCase
{
    use Specify;

    public function testMethod()
    {
        $this->specify("it does something", function () {

            $this->assertTrue(true);
        });
    }
}

