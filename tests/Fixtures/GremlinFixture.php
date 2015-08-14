<?php
namespace Spider\Test\Fixtures;

use brightzone\rexpro\Connection;

class GremlinFixture extends DbFixture
{
    public function load()
    {
        $client = new Connection();
        $client->open('localhost:8182', 'graph');

        $client->send("TinkerFactory.generateModern(graph)");

        $client->close();

        return $this;
    }

    public function unload()
    {
        $client = new Connection();
        $client->open('localhost:8182', 'graph');

        $client->send("g.V().drop().iterate()");

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
