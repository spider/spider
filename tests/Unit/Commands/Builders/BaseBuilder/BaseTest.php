<?php
namespace Spider\Test\Unit\Commands\Builders\BaseBuilder;

use Codeception\Specify;
use Spider\Commands\Bag;
use Spider\Test\Unit\Commands\Builders\TestSetup;

class BaseTest extends TestSetup
{
    use Specify;

    /* Manage the Command Bag */
    public function testClearBag()
    {
        $this->builder
            ->internalRetrieve();

        $this->builder->clear();

        $this->assertEquals([], $this->builder->getBag(), "failed to return an empty bag");
    }

    public function testEnsureBagExists()
    {
        $this->builder->internalProjections('a');

        $this->assertTrue(is_array($this->builder->getBag()), "failed to return an array of bags");
        $this->assertCount(1, $this->builder->getBag(), "failed to return an array of one bag");
        $this->assertInstanceOf('Spider\Commands\Bag', $this->builder->getBag()[0], "failed to return a Command Bag");
    }

    /* Projections tests */
    /* Also thoroughly tests csvToArray() */
    public function testProjections()
    {
        $this->specify("it returns nothing by default", function () {
            $actual = $this->builder
                ->internalRetrieve()
                ->getBag();

            $expected = $this->buildExpectedBag([
                'command' => Bag::COMMAND_RETRIEVE,
                'projections' => []
            ]);

            $this->assertEquals($expected, $actual, "failed to return correct command bag");
        });

        $this->specify("it returns a single value", function () {
            $actual = $this->builder
                ->internalProjections('username')
                ->getBag();

            $expected = $this->buildExpectedBag([
                'projections' => ['username']
            ]);

            $this->assertEquals($expected, $actual, "failed to return correct command bag");
        });

        $this->specify("it several properties from array", function () {
            $actual = $this->builder
                ->internalProjections(['username', 'password'])
                ->getBag();

            $expected = $this->buildExpectedBag([
                'projections' => ['username', 'password']
            ]);

            $this->assertEquals($expected, $actual, "failed to return correct command bag");
        });

        $this->specify("it several properties from csv string (one space)", function () {
            $actual = $this->builder
                ->internalProjections('username, password')
                ->getBag();

            $expected = $this->buildExpectedBag([
                'projections' => ['username', 'password']
            ]);

            $this->assertEquals($expected, $actual, "failed to return correct command bag");
        });

        $this->specify("it returns several properties from csv string (no spaces)", function () {
            $actual = $this->builder
                ->internalProjections('username,password')
                ->getBag();

            $expected = $this->buildExpectedBag([
                'projections' => ['username', 'password']
            ]);

            $this->assertEquals($expected, $actual, "failed to return correct command bag");
        });

        $this->specify("it returns several properties from csv string (many spaces)", function () {
            $actual = $this->builder
                ->internalProjections('username,           password')
                ->getBag();

            $expected = $this->buildExpectedBag([
                'projections' => ['username', 'password']
            ]);

            $this->assertEquals($expected, $actual, "failed to return correct command bag");
        });

        $this->specify("it throws exception if projections is not array or string", function () {
            $this->builder
                ->internalRetrieve()
                ->internalProjections(3)
                ->getBag();

        }, ['throws' => new \InvalidArgumentException("Projections must be a comma-separated string or an array")]);
    }

    public function testMultipleOperations()
    {
        $this->specify("it adds multiple operations in order", function() {
            $this->builder->internalRetrieve('a');
            $this->builder->internalWhere(['one', Bag::COMPARATOR_EQUAL, 'one', Bag::CONJUNCTION_AND]);

            $this->builder->internalCreate([
                'a' => 'A',
                'b' => 'B'
            ]);

            $this->builder->internalUpdate([
                'c' => 'C',
                'd' => 'D'
            ]);

            $this->builder->internalDelete();
            $actual = $this->builder->getBag();

            $expected = $this->buildExpectedBags([
                [
                    'command' => Bag::COMMAND_RETRIEVE,
                    'projections' => ['a'],
                    'where' => [['one', Bag::COMPARATOR_EQUAL, 'one', Bag::CONJUNCTION_AND]],
                ],
                [
                    'command' => Bag::COMMAND_CREATE,
                    'data' => [
                        'a' => 'A',
                        'b' => 'B'
                    ]
                ],
                [
                    'command' => Bag::COMMAND_UPDATE,
                    'data' => [
                        'c' => 'C',
                        'd' => 'D'
                    ]
                ],
                [
                    'command' => Bag::COMMAND_DELETE,
                ]
            ]);

            $this->assertCount(4, $actual, "failed to return four command bags");
            $this->assertEquals($expected, $actual, "failed to return several command bags");
        });
    }

    public function testAliasing()
    {
        $this->specify("it sets and gets operations based on aliases", function() {
            $this->builder->internalRetrieve('a');
            $this->builder->internalWhere(['one', Bag::COMPARATOR_EQUAL, 'one', Bag::CONJUNCTION_AND]);
            $this->builder->set('first');
            $this->builder->set('third');

            $this->builder->internalCreate([
                'a' => 'A',
                'b' => 'B'
            ]);

            $this->builder->internalUpdate([
                'c' => 'C',
                'd' => 'D'
            ]);
            $this->builder->set('second');

            $this->builder->internalDelete();
            $actual = $this->builder->getBag();

            // Check the Bag
            $this->assertTrue(is_array($actual), "failed to return an array");
            $this->assertCount(4, $actual, "failed to return four command bags");

            // Check the Keys
            $keys = array_keys($actual);
            $this->assertEquals(['third', 1, 'second', 3], $keys, "failed to return the correct aliases");

            // Check the individual Bags
            $expected = $this->buildExpectedBags([
                [
                    'command' => Bag::COMMAND_RETRIEVE,
                    'projections' => ['a'],
                    'where' => [['one', Bag::COMPARATOR_EQUAL, 'one', Bag::CONJUNCTION_AND]],
                ],
                [
                    'command' => Bag::COMMAND_CREATE,
                    'data' => [
                        'a' => 'A',
                        'b' => 'B'
                    ]
                ],
                [
                    'command' => Bag::COMMAND_UPDATE,
                    'data' => [
                        'c' => 'C',
                        'd' => 'D'
                    ]
                ],
                [
                    'command' => Bag::COMMAND_DELETE,
                ]
            ]);

            $i = 0;
            foreach ($actual as $bag) {
                $this->assertEquals($expected[$i], $bag, "failed to return the correct bag for index: $i");
                $i++;
            }

            // Check getting by alias
            $this->assertEquals($expected[2], $this->builder->get('second'), "failed to get correct bag");
        });
    }
}
