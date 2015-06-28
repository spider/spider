<?php
namespace Michaels\Spider\Test\Stubs;

class SecondDriverStub extends DriverStub
{
    public function open(array $credentials, array $config = [])
    {
        return [
            'credentials' => $credentials,
            'config' => $config
        ];
    }
}
