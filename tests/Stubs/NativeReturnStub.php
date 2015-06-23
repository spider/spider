<?php
namespace Michaels\Spider\Test\Stubs;

/**
 * Class NativeReturnStub
 * @package Michaels\Spider\Test\Stubs
 */
class NativeReturnStub
{
    public $property = 'is set';

    public function __construct(array $data)
    {
        foreach ($data as $key => $value) {
            $this->$key = $value;
        }
    }
}
