<?php
/**
 * Created by PhpStorm.
 * User: Michael
 * Date: 5/26/2015
 * Time: 4:41 PM
 */
namespace Michaels\Spider\Drivers;


/**
 * Class OrientDriver
 * @package Michaels\Spider\Drivers\OrientDB
 */
interface DriverInterface
{
    public function connect($properties);

    public function listDatabases();

    public function open($database);

    public function close();

    public function addVertex($name, $properties);

    public function addEdge($name, $properties);

    public function updateVertex($name, $properties);

    public function getVertex($name);

    public function getEdge();

    public function dropVertex();

    public function dropEdge();

    public function statement($statement);

    public function query($statement);
}