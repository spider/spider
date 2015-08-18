<?php
namespace Spider\Test\Unit\Commands\Builders\Builder;

use Codeception\Specify;
use Spider\Commands\Bag;

class UpdateTest extends TestSetup
{
    use Specify;

    public function testUpdateFirst()
    {
        $this->specify("it updates a single record using updateFirst and data", function () {
            $actual = $this->builder
                ->updateFirst()
                ->from('users')
                ->where('username', 'chrismichaels84')
                ->data('name', 'chris')
                ->getBag();

            $expected = $this->buildExpectedBag([
                'command' => Bag::COMMAND_UPDATE,
                'target' => Bag::ELEMENT_VERTEX,
                'limit' => 1,
                'where' => [
                    [Bag::ELEMENT_LABEL, Bag::COMPARATOR_EQUAL, "users", Bag::CONJUNCTION_AND],['username', Bag::COMPARATOR_EQUAL, 'chrismichaels84', Bag::CONJUNCTION_AND]],
                'data' => [['name' => 'chris']]
            ]);

            $this->assertEquals($expected, $actual, "failed to return correct command bag");
        });
    }
}
