<?php

namespace Michaels\Spider\Drivers;


/**
 * Class OrientDriver
 * @package Michaels\Spider\Drivers\OrientDB
 */
interface DriverInterface
{
    public function connect($properties);

    public function listDbs();

    public function openDb($database);

    public function closeDb();

    public function addVertex($properties);

    public function addEdge($from, $to, $properties);

    public function updateVertex($id, $properties);

    public function getVertex($id);

    public function getEdge($id);

    public function dropVertex($id);

    public function dropEdge($id);

    public function statement($statement);

    public function query($statement);
}