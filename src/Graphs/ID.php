<?php
namespace Spider\Graphs;

class ID
{
    /** @var string|int The Id of the current Record */
    public $id;

    /**
     * Constructs a new ID
     * @param mixed $id
     */
    public function __construct($id = null)
    {
        if ($id) {
            $this->id = $id;
        }
    }

    /**
     * Casts $id to string if needed
     * @return string
     */
    public function __toString()
    {
        return (string)$this->id;
    }
}
