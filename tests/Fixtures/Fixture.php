<?php
namespace Spider\Test\Fixtures;

abstract class Fixture
{
    abstract public function load();

    abstract public function unload();

    abstract public function getData();

    abstract public function setDependencies();

    abstract public function getDependencies();
}
