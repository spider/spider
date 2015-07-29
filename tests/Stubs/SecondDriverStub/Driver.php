<?php
namespace Spider\Test\Stubs\SecondDriverStub;

use Spider\Test\Stubs\FirstDriverStub\Driver as FirstDriver;

class Driver extends FirstDriver
{
    public $port;
    public $hostname;

    public function open()
    {
        $config = [];
        foreach ($this as $property => $value) {
            $config[$property] = $value;
        }

        return $config;
    }
}
