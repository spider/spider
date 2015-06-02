<?php
namespace Michaels\Spider\Drivers;

interface GremlinDriverInterface extends DriverInterface
{
    /**
     * Execute a Gremlin Script
     * @return mixed
     */
    public function gremlin();
}
