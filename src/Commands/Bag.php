<?php
namespace Michaels\Spider\Commands;

/**
 * Class Bag
 * @package Michaels\Spider\Queries
 */
class Bag
{
    public $command = null;
    public $projections = [];
    public $from = null;
    public $limit = null;
    public $groupBy = null;
    public $orderBy = null;
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
