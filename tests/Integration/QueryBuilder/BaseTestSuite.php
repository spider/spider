<?php
namespace Spider\Test\Integration\QueryBuilder;

use Codeception\Specify;
use Spider\Commands\Languages\OrientSQL\CommandProcessor;
use Spider\Test\Fixtures\Graph;
use Spider\Commands\Bag;

abstract class BaseTestSuite extends \PHPUnit_Framework_TestCase
{
    use Specify;

    protected $expected = [
        [
            'label' => 'person',
            'name' => 'marko',
            'age' => 29,
            'out' => [
                [
                    'label' => 'knows',
                    'weight' => 0.5,
                    'to' => 1
                ],
                [
                    'label' => 'created',
                    'weight' => 0.4,
                    'to' => 4
                ],
            ]
        ],
        [
            'label' => 'person',
            'name' => 'vadas',
            'age' => 27,
            'in' => [
                [
                    'label' => 'knows',
                    'weight' => 0.5,
                    'from' => 0
                ],
            ]
        ],
        [
            'label' => 'person',
            'name' => 'peter',
            'age' => 35,
            'out' => [
                [
                    'label' => 'created',
                    'weight' => 0.2,
                    'to' => 4
                ],
            ]
        ],
        [
            'label' => 'person',
            'name' => 'josh',
            'age' => 32,
            'out' => [
                [
                    'label' => 'created',
                    'weight' => 0.4,
                    'to' => 4
                ],
                [
                    'label' => 'created',
                    'weight' => 1.0,
                    'to' => 5
                ],
            ]
        ],
        [
            'label' => 'software',
            'name' => 'lop',
            'lang' => 'java',
            'in' => [
                [
                    'label' => 'created',
                    'weight' => 0.4,
                    'from' => 0
                ],
                [
                    'label' => 'created',
                    'weight' => 0.2,
                    'from' => 2
                ],
                [
                    'label' => 'created',
                    'weight' => 0.4,
                    'from' => 3
                ]
            ]
        ],
        [
            'label' => 'software',
            'name' => 'ripple',
            'lang' => 'java',
            'in' => [
                [
                    'label' => 'created',
                    'weight' => 1.0,
                    'from' => 3
                ],
            ],
        ]
    ];
    protected $query;
    protected $fixture;

    public function testDispatching()
    {
        $this->specify("it dispatches using `dispatch`", function () {
            $response = $this->query
                ->select()
                ->dispatch();

            $expected = $this->expected;

            $this->assertInstanceOf('Spider\Drivers\Response', $response, "failed to return a `Response`");
            $consistent = $response->getSet();

            $this->assertTrue(is_array($consistent), 'failed to return an array');
            $this->assertCount(count($expected), $consistent, 'failed to return the correct number of records');
            $this->assertInstanceOf('Spider\Base\Collection', $consistent[0], 'failed to return an array of collections');
            $this->assertEquals($expected[0]['name'], $consistent[0]->name, 'failed to return correct first collection');
        });

        $this->specify("it dispatches using `go`", function () {
            $response = $this->query
                ->select()
                ->go();

            $expected = $this->expected;

            $this->assertInstanceOf('Spider\Drivers\Response', $response, "failed to return a `Response`");
            $consistent = $response->getSet();

            $this->assertTrue(is_array($consistent), 'failed to return an array');
            $this->assertCount(count($expected), $consistent, 'failed to return the correct number of records');
            $this->assertInstanceOf('Spider\Base\Collection', $consistent[0], 'failed to return an array of collections');
            $this->assertEquals($expected[0]['name'], $consistent[0]->name, 'failed to return correct first collection');
        });

        $this->specify("it dispatches using `all`", function () {
            $response = $this->query
                ->select()
                ->all();

            $expected = $this->expected;

            $this->assertTrue(is_array($response), 'failed to return an array');
            $this->assertCount(count($expected), $response, 'failed to return the correct number of records');
            $this->assertInstanceOf('Spider\Base\Collection', $response[0], 'failed to return an array of collections');
            $this->assertEquals($expected[0]['name'], $response[0]->name, 'failed to return correct first collection');
        });

        $this->specify("it dispatches using `get` and `set`", function () {
            $response = $this->query
                ->select()
                ->get();

            $expected = $this->expected;

            $this->assertTrue(is_array($response), 'failed to return an array');
            $this->assertCount(count($expected), $response, 'failed to return the correct number of records');
            $this->assertInstanceOf('Spider\Base\Collection', $response[0], 'failed to return an array of collections');
            $this->assertEquals($expected[0]['name'], $response[0]->name, 'failed to return correct first collection');
        });

        $this->specify("it selects one record with `one` and `first`", function () {
            $response = $this->query
                ->select()
                ->first();

            $expected = $this->expected[0];

            $this->assertFalse(is_array($response), 'failed: returned an array instead of a collection');
            $this->assertInstanceOf('Spider\Base\Collection', $response, 'failed to return an array of collections');
            $this->assertEquals($expected['name'], $response->name, 'failed to return correct first collection');
        });

    }

    public function testFormattedDispatches()
    {
        $this->specify("it formats as a scalar value", function () {
            $response = $this->query
                ->select('name')
                ->from('person')
                ->scalar();

            $expected = $this->expected[0]['name'];

            $this->assertTrue(is_string($response), 'failed to return a string');
            $this->assertEquals($expected, $response, 'failed to return the correct scalar');
        });

        $this->specify("it throws exception for multiple projections", function() {
            $this->query
                ->select()
                ->from('person')
                ->scalar();
        }, ['throws' => 'Spider\Exceptions\FormattingException']);

        /* Test path and tree once they are supported */
    }

    public function testBasicSelects()
    {
        $this->specify("it selects all records from a label", function () {
            $response = $this->query
                ->select()
                ->from('person')
                ->all();

            $expected = array_filter($this->expected, function ($record) {
                return $record['label'] === 'person';
            });

            $this->assertTrue(is_array($response), 'failed to return an array');
            $this->assertCount(count($expected), $response, 'failed to return the correct number of records');
            $this->assertInstanceOf('Spider\Base\Collection', $response[0], 'failed to return an array of collections');
            $this->assertEquals($expected[0]['name'], $response[0]->name, 'failed to return correct first collection');
        });

        $this->specify("it selects one record with first", function () {
            $response = $this->query
                ->select()
                ->from('person')
                ->first();

            $expected = $this->expected;

            $this->assertFalse(is_array($response), 'failed to return a collection array');
            $this->assertInstanceOf('Spider\Base\Collection', $response, 'failed to return an array of collections');
            $this->assertEquals($expected[0]['name'], $response->name, 'failed to return correct first collection');
        });

        $this->specify("it selects with constraints", function () {
            $response = $this->query
                ->select()
                ->from('person')
                ->where('name', 'marko')
                ->andWhere('age', 29)
                ->all();

            $expected = array_filter($this->expected, function ($record) {
                return $record['label'] === 'person'
                && $record['name'] === 'marko'
                && $record['age'] === 29;
            });

            $this->assertTrue(is_array($response), 'failed to return an array');
            $this->assertCount(1, $response, 'failed to return one Collection');
            $this->assertInstanceOf('Spider\Base\Collection', $response[0], 'failed to return an array of collections');
            $this->assertEquals($expected[0]['name'], $response[0]->name, 'failed to return correct first collection');
        });

        $this->specify("it selects with OR constraints", function () {
            $response = $this->query
                ->select()
                ->from('person')
                ->where('name', 'marko')
                ->orWhere('name', 'peter')
                ->orderBy('name')
                ->all();

            $expected = array_filter($this->expected, function ($record) {
                return $record['label'] === 'person'
                && ($record['name'] === 'marko' || $record['name'] === 'peter');
            });

            $expected = array_values($expected);

            $this->assertTrue(is_array($response), 'failed to return an array');
            $this->assertCount(count($expected), $response, 'failed to return the correct number of records');

            $this->assertInstanceOf('Spider\Base\Collection', $response[0], 'failed to return an array of collections');
            $this->assertEquals($expected[0]['name'], $response[0]->name, 'failed to return correct first collection');

            $this->assertInstanceOf('Spider\Base\Collection', $response[1], 'failed to return an array of collections');
            $this->assertEquals($expected[1]['name'], $response[1]->name, 'failed to return correct first collection');
        });

        $this->specify("it selects with limits", function () {
            $response = $this->query
                ->select()
                ->from('person')
                ->limit(3)
                ->orderBy('name')
                ->get();

            /* Setup the expected data */
            // only persons
            $expected = array_filter($this->expected, function ($record) {
                return $record['label'] === 'person';
            });

            // In the right order
            foreach ($expected as $key => $row) {
                $name[$key]  = $row['name'];
            }
            array_multisort($name, SORT_ASC, $expected);

            // First three results
            $expected = array_slice($expected, 0, 3);
            $expected = array_values($expected);

            /* Assertions */
            $this->assertTrue(is_array($response), 'failed to return an array');
            $this->assertCount(3, $response, 'failed to return the correct number of records');

            $this->assertInstanceOf('Spider\Base\Collection', $response[0], 'failed to return an array of collections');
            $this->assertEquals($expected[0]['name'], $response[0]->name, 'failed to return correct first collection');

            $this->assertInstanceOf('Spider\Base\Collection', $response[1], 'failed to return an array of collections');
            $this->assertEquals($expected[1]['name'], $response[1]->name, 'failed to return correct first collection');

            $this->assertInstanceOf('Spider\Base\Collection', $response[2], 'failed to return an array of collections');
            $this->assertEquals($expected[2]['name'], $response[2]->name, 'failed to return correct first collection');
        });
    }

    public function testInsertAndDeleteById()
    {
        $this->specify("it inserts and deletes a single record", function () {
            $record = [
                'first' => 'first-value',
                'second' => 'second-value',
                Bag::ELEMENT_LABEL => 'person',
            ];

            $this->query
                ->insert($record)
                ->go();

            $response = $this->query
                ->select()
                ->from('person')
                ->where('first', 'first-value')
                ->one();

            $this->assertInstanceOf('Spider\Base\Collection', $response, "failed to return one record");
            $this->assertEquals("second-value", $response->second, "failed to create record");

            // Delete
            $this->query
                ->drop($response->id)->go();

            // Check for it again
            $response = $this->query
                ->select()
                ->from('person')
                ->where('first', 'first-value')
                ->all();

            $this->assertEmpty($response, "failed to return empty array for no items");

            // Make sure we didn't delete everything
            $response = $this->query
                ->select()
                ->all();

            $this->assertCount(6, $response, "failed to leave six other records");
        });

        /* @todo Neo fails this because of dropping multiple records by a list of ids */
        $this->specify("it inserts and deletes multiple records", function () {
            $records = [
                [
                    'name' => 'michael',
                    'second' => 'second-value',
                    Bag::ELEMENT_LABEL => 'person',
                ],
                [
                    'name' => 'michael',
                    'fourth' => 'fourth-value',
                    Bag::ELEMENT_LABEL => 'person',
                ],
            ];

            $this->query
                ->insert($records)
                ->go();

            $response = $this->query
                ->select()
                ->from('person')
                ->where('name', 'michael')
                ->all();

            $this->assertTrue(is_array($response), "failed to return an array");
            $this->assertCount(2, $response, "failed to return two records");

            $this->assertInstanceOf('Spider\Base\Collection', $response[0], "failed to return one record");
            $this->assertEquals("second-value", $response[0]->second, "failed to create record");

            $this->assertInstanceOf('Spider\Base\Collection', $response[1], "failed to return one record");
            $this->assertEquals("fourth-value", $response[1]->fourth, "failed to create record");

            // Delete
            $this->query
                ->drop([$response[0]->id, $response[1]->id])->go();

            // Check for it again
            $response = $this->query
                ->select()
                ->from('person')
                ->where('name', 'michael')
                ->all();

            $this->assertEmpty($response, "failed to return empty array for no items");

            // Make sure we deleted what we wanted to
            $response = $this->query
                ->select()
                ->all();

            $this->assertCount(6, $response, "failed to leave six other records");
        });
    }

    public function testDrop()
    {
        $this->specify("it drops a single record via `dispatch()`", function () {
            $recordToDelete = $this->query->select()->from('person')->where('name', 'marko')->one();

            $this->query
                ->drop()
                ->record($recordToDelete->id)
                ->dispatch();

            // Now, try to find it again
            $actual = $this->query->select()->record($recordToDelete->id)->one();

            $this->assertEmpty($actual, "failed to delete record");
        });

        $this->specify("it drops multiple records dispatching from `dispatch()`", function () {
            $recordsToDelete = $this->query->select()->from('person')->where('lang', 'java')->all();
            $ids = [];
            foreach ($recordsToDelete as $record) {
                $ids[] = $record->id;
            }

            $this->query
                ->drop()
                ->record($ids)
                ->dispatch();

            // Now, try to find it again
            $actual = $this->query->select()->from('person')->where('lang', 'java')->all();
            $this->assertEmpty($actual, "failed to delete record");
        });
    }

    public function testUpdates()
    {
        $this->specify("it updates a key and a value on several records", function () {
            $this->query
                ->update('name', 'new_name')
                ->where(Bag::ELEMENT_LABEL, 'person')
                ->go(); // All person are now named 'new_name'

            // Check our work
            $response = $this->query
                ->select()
                ->from('person')
                ->where('name', 'new_name')
                ->all();

            $this->assertTrue(is_array($response), "failed to return an array");
            $this->assertCount(4, $response, "failed to return two records");
        });

        $this->specify("it updates a single record by id", function () {
            $record = $this->query
                ->select()->first();

            $this->query
                ->update(['name' => 'new_name', 'other' => 'value'])
                ->record($record->id)
                ->go();

            // Check our work
            $response = $this->query
                ->select()
                ->from('person')
                ->where('name', 'new_name')
                ->all();

            $this->assertTrue(is_array($response), "failed to return an array");
            $this->assertCount(1, $response, "failed to return two records");
            $this->assertEquals('value', $response[0]->other, "failed to add new data");
        });

        $this->specify("it updates a single record with several data by wheres", function () {
            $this->query
                ->update(['name' => 'new_name', 'other' => 'value'])
                ->where('name', 'marko')
                ->andWhere(Bag::ELEMENT_LABEL, 'person')
                ->go();

            // Check our work
            $response = $this->query
                ->select()
                ->from('person')
                ->where('name', 'new_name')
                ->all();

            $this->assertTrue(is_array($response), "failed to return an array");
            $this->assertCount(1, $response, "failed to return two records");
            $this->assertEquals('value', $response[0]->other, "failed to add new data");
        });

        $this->specify("it updates the first record with data", function () {
            $this->query
                ->update()
                ->where(Bag::ELEMENT_LABEL, 'person')
                ->withData(['name' => 'new_name', 'other' => 'value'])
                ->first();

            // Check our work
            $response = $this->query
                ->select()
                ->from('person')
                ->where('name', 'new_name')
                ->all();

            $this->assertTrue(is_array($response), "failed to return an array");
            $this->assertCount(1, $response, "failed to return two records");
            $this->assertEquals('value', $response[0]->other, "failed to add new data");
        });
    }
}
