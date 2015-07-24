<?php
namespace Spider\Drivers;

use Spider\Base\Collection;

/**
 * Consistent Driver response object.
 */
class Response extends Collection
{
    /**
     * @var mixed The raw response from the database
     */
    protected $_raw;

    /**
     * @var Driver The driver this response is attached to.
     */
    protected $_driver;

    /**
     * Get the raw db response
     *
     * @return mixed
     */
    public function getRaw()
    {
        return $this->_raw;
    }

    /**
     * Get a set of collections
     *
     * @return array An array of collections (vertices or edges)
     */
    public function getSet()
    {
        return $this->_driver->formatAsSet($this->_raw);
    }

    /**
     * Get a tree of collections
     *
     * @return array An array of collections (vertices or edges) in tree format
     */
    public function getTree()
    {
        return $this->_driver->formatAsTree($this->_raw);
    }

    /**
     * Get a path of collections
     *
     * @return array An array of collections (vertices or edges) in path format
     */
    public function getPath()
    {
        return $this->_driver->formatAsPath($this->_raw);
    }

    /**
     * Get a scalar value from response
     *
     * @return mixed Scalar
     */
    public function getScalar()
    {
        return $this->_driver->formatAsScalar($this->_raw);
    }
}
