<?php
namespace Spider\Test\Fixtures;

use Brightzone\GremlinDriver\Connection;

class GremlinFixture extends DbFixture
{
    public function load()
    {
        $host = getenv('GREMLIN_HOSTNAME');
        $port = getenv('GREMLIN_PORT');

        $client = new Connection();
        $client->open("{$host}:{$port}", 'graph');

        try{
            $client->send("TinkerFactory.generateModern(graph)");
        } catch (\Exception $e) {
            //Check for empty return error from server.
            if (!($e instanceof \Brightzone\GremlinDriver\ServerException) || ($e->getCode() != 204)) {
                throw $e;
            }
        }

        $client->close();

        return $this;
    }

    public function unload()
    {
        $host = getenv('GREMLIN_HOSTNAME');
        $port = getenv('GREMLIN_PORT');
        
        $client = new Connection();
        $client->open("{$host}:{$port}", 'graph');

        try {
            $client->send("g.V().drop().iterate()");
        } catch (\Exception $e) {
            //Check for empty return error from server.
            if (!($e instanceof \Brightzone\GremlinDriver\ServerException) || ($e->getCode() != 204)) {
                throw $e;
            }
        }
        $client->close();
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
