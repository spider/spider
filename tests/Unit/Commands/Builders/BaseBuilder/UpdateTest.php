<?php
namespace Spider\Test\Unit\Commands\Builders\BaseBuilder;

use Codeception\Specify;
use Spider\Commands\Bag;
use Spider\Test\Unit\Commands\Builders\TestSetup;

class UpdateTest extends TestSetup
{
    use Specify;

    /* Update Tests */
    public function testUpdateRecords()
    {
        $this->specify("it updates a single record with a single value by ID", function () {
            $actual = $this->builder
                ->internalUpdate(['name'=> 'chris'])
                ->getBag();

            $expected = $this->buildExpectedBag([
                'update' => ['name' => 'chris']
            ]);

            $this->assertEquals($expected, $actual, "failed to return correct command bag");
        });

        $this->specify("it updates multiple properties", function () {
            $actual = $this->builder
                ->internalUpdate([
                    'name' => 'chris',
                    'birthday' => 'april'
                ])
                ->getBag();

            $expected = $this->buildExpectedBag([
                'update' => ['name' => 'chris', 'birthday' => 'april'],
            ]);

            $this->assertEquals($expected, $actual, "failed to return correct command bag");
        });
    }
}
