<?php
namespace Spider\Test\Fixtures;

abstract class Fixture
{
    abstract public function setup();

    abstract protected function teardown();

    abstract protected function reset();
}
