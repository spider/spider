<?php
namespace Michaels\Spider\Test\Stubs;

/**
 * Class NativeReturnStub
 * @package Michaels\Spider\Test\Stubs
 */
class SpecificReturnMapMethodStub
{
    public $property = 'is set';

    public function map($data)
    {
        foreach ($data as $key => $value) {
            $this->$key = $value;
        }
    }
}
