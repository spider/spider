<?php
namespace Spider\Test\Unit\Commands\Builders\Builder;

use Codeception\Specify;
use Spider\Commands\Bag;
use Spider\Graphs\ID as TargetID;

class UpdateTest extends TestSetup
{
    use Specify;

    public function testUpdateFirst()
    {
        $this->specify("it updates a single record using updateFirst and data", function () {
            $actual = $this->builder
                ->updateFirst('users')
                ->where('username', 'chrismichaels84')
                ->data('name', 'chris')
                ->getCommandBag();

            $expected = $this->buildExpectedBag([
                'command' => Bag::COMMAND_UPDATE,
                'target' => 'users',
                'limit' => 1,
                'where' => [['username', Bag::COMPARATOR_EQUAL, 'chrismichaels84', Bag::CONJUNCTION_AND]],
                'data' => ['name' => 'chris']
            ]);

            $this->assertEquals($expected, $actual, "failed to return correct command bag");
        });
    }
}
