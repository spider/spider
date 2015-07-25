<?php
namespace Spider\Base;

/**
 * Class Object
 * @package Spider\Base
 */
class Object {

    /**
     * Overriding construct to map configuration to properties
     *
     * @param array $configuration the properties to populate
     *
     */
    public function __construct(array $configuration = [])
    {
        $this->setProperties($configuration);
    }

    /**
     * Map array to class properties
     *
     * @param array $properties the key value array to set
     *
     * @return void
     */
    public function setProperties(array $properties)
    {
        foreach($properties as $key => $value)
        {
            $this->setProperty($key, $value);
        }
    }

    /**
     * Set a class property
     *
     * @param String|Int $name  the name of the property to set
     * @param mixed      $value the value of the property
     *
     * @return void
     */
    public function setProperty($name, $value)
    {
        $this->$name = $value;
    }
}
