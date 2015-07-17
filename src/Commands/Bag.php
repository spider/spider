<?php
namespace Michaels\Spider\Commands;

/**
 * Class Bag
 * @package Michaels\Spider\Queries
 */
class Bag
{
    /* Bag Contents */
    public $command = null;
    public $projections = [];
    public $from = null;
    public $limit = null;
    public $groupBy = null;
    public $orderBy = null;
    public $orderAsc = true;
    public $where = [];

    /* Constants */
    const COMPARATOR_EQUAL = 'EQUAL'; // =
    const COMPARATOR_LT = 'LT'; // <
    const COMPARATOR_GT = 'GT'; // >
    const COMPARATOR_GE = 'GE'; // >=
    const COMPARATOR_LE = 'LE'; // <=
    const COMPARATOR_NE = 'NE'; // not equal
    const COMPARATOR_WITHOUT = 'WITHOUT';

    const CONJUNCTION_AND = 'AND';
    const CONJUNCTION_OR = 'OR';


    public function __construct(array $properties = null)
    {
        if ($properties) {
            foreach ($properties as $key => $value) {
                $this->$key = $value;
            }
        }
    }
}
