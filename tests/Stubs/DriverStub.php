<?php
namespace Michaels\Spider\Test\Stubs;

use Michaels\Spider\Drivers\DriverInterface;

class DriverStub implements DriverInterface
{

    public function connect($properties)
    {
        // TODO: Implement connect() method.
    }

    public function listDbs()
    {
        // TODO: Implement listDbs() method.
    }

    public function openDb($database)
    {
        // TODO: Implement openDb() method.
    }

    public function closeDb()
    {
        // TODO: Implement closeDb() method.
    }

    public function addVertex($properties)
    {
        // TODO: Implement addVertex() method.
    }

    public function addEdge($from, $to, $properties)
    {
        // TODO: Implement addEdge() method.
    }

    public function updateVertex($id, $properties)
    {
        // TODO: Implement updateVertex() method.
    }

    public function getVertex($id)
    {
        // TODO: Implement getVertex() method.
    }

    public function getEdge($id)
    {
        // TODO: Implement getEdge() method.
    }

    public function dropVertex($id)
    {
        // TODO: Implement dropVertex() method.
    }

    public function dropEdge($id)
    {
        // TODO: Implement dropEdge() method.
    }

    public function statement($statement)
    {
        // TODO: Implement statement() method.
    }

    public function query($statement)
    {
        // TODO: Implement query() method.
    }
}
