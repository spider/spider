<?php
namespace Spider\Test\Unit\Commands;

use Codeception\Specify;
use Spider\Commands\Command;

/**
 * Tests the command class
 */
class CommandTest extends \PHPUnit_Framework_TestCase
{
    public function testSetGetScript()
    {
        $command = new Command();
        $command->setScript('some script');
        $this->assertEquals('some script', $command->getScript(), 'Incorrect script output');
    }

    public function testSetGetLanguage()
    {
        $command = new Command();
        $command->setScriptLanguage('some-language');
        $this->assertEquals('some-language', $command->getScriptLanguage(), 'Incorrect script language output');
    }

    public function testConstruct()
    {
        $command = new Command('some script', 'some-language');
        $this->assertEquals('some script', $command->getScript(), 'Incorrect script output');
        $this->assertEquals('some-language', $command->getScriptLanguage(), 'Incorrect script language output');
    }

    public function testToString()
    {
        $command = new Command('some script', 'some-language');
        $this->assertEquals('some script', $command, 'toString does not work correctly');
    }
}
