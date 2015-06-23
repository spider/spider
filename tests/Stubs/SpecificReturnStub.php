<?php
namespace Michaels\Spider\Test\Stubs;

/**
 * Class NativeReturnStub
 * @package Michaels\Spider\Test\Stubs
 */
class SpecificReturnStub
{
    public $property = 'is set';

    public function __construct($data)
    {
        foreach ($data as $key => $value) {
            $this->$key = $value;
        }
    }
}
