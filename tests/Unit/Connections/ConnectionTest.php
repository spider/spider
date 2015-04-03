<?php
namespace Michaels\Spider\Test\Unit\Connections;

use Codeception\Specify;

class ConnectionTest extends \PHPUnit_Framework_TestCase
{
    use Specify;

    public function testMethod()
    {
        $this->specify("it does something", function() {
           
            $this->assertTrue(true);
        });
    }
}

