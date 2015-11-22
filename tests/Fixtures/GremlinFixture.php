<?php
namespace Spider\Test\Fixtures;

use brightzone\rexpro\Connection;

class GremlinFixture extends DbFixture
{
    public function load()
    {
        $client = new Connection();
        $client->open('localhost:8182', 'graph');

        try{
            $client->send("TinkerFactory.generateModern(graph)");
        } catch (\Exception $e) {
            //Check for empty return error from server.
            if (!($e instanceof \brightzone\rexpro\ServerException) || ($e->getCode() != 204)) {
                throw $e;
            }
        }

        $client->close();

        return $this;
    }

    public function unload()
    {
        $client = new Connection();
        $client->open('localhost:8182', 'graph');

        try {
            $client->send("g.V().drop().iterate()");
        } catch (\Exception $e) {
            //Check for empty return error from server.
            if (!($e instanceof \brightzone\rexpro\ServerException) || ($e->getCode() != 204)) {
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
