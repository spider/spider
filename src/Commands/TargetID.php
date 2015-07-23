<?php
namespace Spider\Commands;

class TargetID
{
    public $id;

    public function __construct($id = null)
    {
        if ($id) {
            $this->id = $id;
        }
    }

    public function __toString()
    {
        return (string)$this->id;
    }
}
