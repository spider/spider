<?php
namespace Spider\Drivers;

abstract class AbstractDriver implements DriverInterface
{
    /**
     * Constructs a driver from a properties array
     * @param array $properties
     */
    public function __construct(array $properties = [])
    {
        $this->setCredentials($properties);
    }

    /**
     * Sets the credentials from a properties array
     * @param array $properties
     */
    public function setCredentials(array $properties = [])
    {
        foreach ($properties as $property => $value) {
            $this->setCredential($property, $value);
        }
    }

    /**
     * Sets and individual credential configuration item
     * @param $property
     * @param $value
     * @return $this
     */
    public function setCredential($property, $value)
    {
        $this->$property = $value;
        return $this;
    }

    /**
     * Returns an individual configuration item or fallback
     *
     * Throws exception if nothing is found and no fallback
     * @param $property
     * @param null $fallback
     * @return null
     */
    public function getCredential($property, $fallback = null)
    {
        if ($this->$property) {
            return $this->$property;
        }

        if ($fallback) {
            return $fallback;
        }

        throw new \InvalidArgumentException("$property does not exist in this driver");
    }
}
