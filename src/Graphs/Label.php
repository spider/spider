<?php
namespace Spider\Graphs;

class Label
{
    /** @var string The Id of the current Record */
    public $label;

    /**
     * Constructs a new ID
     * @param mixed $label
     */
    public function __construct($label = null)
    {
        if ($label) {
            $this->label = (string)$label;
        }
    }

    /**
     * Casts $id to string if needed
     * @return string
     */
    public function __toString()
    {
        return $this->label;
    }
}
