<?php
namespace Michaels\Spider\Test\Stubs\SecondDriverStub;

use Michaels\Spider\Test\Stubs\FirstDriverStub\Driver as FirstDriver;

class Driver extends FirstDriver
{
    public function open(array $credentials, array $config = [])
    {
        return [
            'credentials' => $credentials,
            'config' => $config
        ];
    }
}
