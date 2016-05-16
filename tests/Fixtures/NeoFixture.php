<?php
namespace Spider\Test\Fixtures;
use Everyman\Neo4j\Client;
use Everyman\Neo4j\Cypher\Query;
use PhpOrient\PhpOrient;

/**
 * Class OrientFixture
 * @package Spider\Test\Fixtures
 */
class NeoFixture extends DbFixture
{
    public function load()
    {
        $client = new Client(getenv('NEO4J_HOSTNAME'), getenv('NEO4J_PORT'));
        $client->getTransport()
            ->setAuth(getenv('NEO4J_USERNAME'), getenv('NEO4J_PASSWORD'));

        $queryString = "CREATE (a:person {name:'marko',age:29 }),
            (b:person {name:'vadas',age:27 }),
            (c:person {name:'peter',age:35 }),
            (d:person {name:'josh',age:32 }),
            (e:software {name:'lop',lang:'java' }),
            (f:software {name:'ripple',lang:'java' }),
            (a)-[:knows {weight:0.5}]->(b),
            (a)-[:created {weight:0.4}]->(e),
            (c)-[:created {weight:0.2}]->(e),
            (d)-[:created {weight:0.4}]->(e),
            (d)-[:created {weight:1.0}]->(f)";
        $query = new Query($client, $queryString);
        $query->getResultSet();

        return $this;
    }

    public function unload()
    {
        $client = new Client(getenv('NEO4J_HOSTNAME'), getenv('NEO4J_PORT'));
        $client->getTransport()
            ->setAuth(getenv('NEO4J_USERNAME'), getenv('NEO4J_PASSWORD'));

        $queryString = "MATCH (n)
            OPTIONAL MATCH (n)-[r]-()
            DELETE n,r";

        $query = new Query($client, $queryString);
        $query->getResultSet();
    }

    public function setDependencies()
    {
        // nothing
    }

    public function getDependencies()
    {
        // Nothing
    }
}
