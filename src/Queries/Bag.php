<?php
namespace Michaels\Spider\Queries;

/**
 * Class Bag
 * @package Michaels\Spider\Queries
 */
class Bag
{
    public $command;
    public $projections;
    public $from;
    public $limit;
    public $groupBy;
    public $orderBy;
    public $orderAsc = true;
    public $where = [];

    public function __construct(array $properties = null)
    {
        if ($properties) {
            foreach ($properties as $key => $value) {
                $this->$key = $value;
            }
        }
    }
}
