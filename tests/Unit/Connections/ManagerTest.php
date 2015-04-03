<?php
namespace Michaels\Spider\Test\Unit\Connections;

use Codeception\Specify;
use Michaels\Spider\Connections\Manager;

class ManagerTest extends \PHPUnit_Framework_TestCase
{
    use Specify;

    public function testInitialization()
    {
        $this->specify("it initializes with no config items", function() {
            $manager = new Manager();

            $this->assertTrue($manager);
        });
    }
}
