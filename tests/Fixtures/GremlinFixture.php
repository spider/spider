<?php
namespace Spider\Test\Fixtures;

use Brightzone\GremlinDriver\Connection;

class GremlinFixture extends DbFixture
{
    public function load()
    {
        $client = new Connection([
            'host' => getenv('GREMLIN_HOSTNAME'),
            'port' => getenv('GREMLIN_PORT'),
            'graph' => 'graph'
        ]);
        $client->open();

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
        $client = new Connection([
            'host' => getenv('GREMLIN_HOSTNAME'),
            'port' => getenv('GREMLIN_PORT'),
            'graph' => 'graph'
        ]);
        $client->open();

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
