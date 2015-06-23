<?php
namespace Michaels\Spider\Graphs;

use Michaels\Manager\Traits\ManagesItemsTrait;

/**
 * Class GraphCollection
 * @package Michaels\Spider\Graphs
 */
class Graph implements GraphInterface
{
    use ManagesItemsTrait;

    /**
     * Creates  new instance of a Graph
     *
     * @param $data
     */
    public function __construct($data)
    {

    }

    /**
     * Returns the raw data
     */
    public function getRaw()
    {

    }
}
