<?php
namespace Spider\Test\Fixtures;

/**
 * Class Graph
 * @package Spider\Test\Fixtures
 */
class Graph
{
    public static $servers = [
        'orient' => [
            'hostname' => 'localhost',
            'port' => 2424,
            'username' => 'root',
            'password' => "root",
            'database' => 'spider_test_graph',
            'driver' => 'orientdb',
        ],
        'neo4j' => [
            'hostname' => 'localhost',
            'port' => 7474,
            'username' => "neo4j",
            'password' => "j4oen",
            'driver' => 'neo4j',
        ],
        'gremlin' => [
            'hostname' => 'localhost',
            'port' => 8182,
            'graph' => 'graph',
            'traversal' => 'g',
            'driver' => 'gremlin',
        ],
        'gremlin-transaction' => [
            'hostname' => 'localhost',
            'port' => 8182,
            'graph' => 'graphT',
            'traversal' => 't',
            'driver' => 'gremlin',
        ]
    ];

    public static $data = [
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
            'label' => 'person',
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
            'label' => 'person',
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
}
