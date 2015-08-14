<?php
namespace Spider\Test\Fixtures;

abstract class Fixture
{
    protected $data;

    abstract public function load();

    abstract public function unload();

    abstract public function getData();

    abstract public function setDependencies();

    abstract public function getDependencies();
}
