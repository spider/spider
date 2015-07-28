<?php
namespace Spider\Base;

use Michaels\Manager\Traits\ManagesItemsTrait;
use Michaels\Manager\Traits\ChainsNestedItemsTrait;

/**
 * Class Colection
 * @package Spider\Base
 */
class Collection extends Object
{
     /**
     * @inherits from Michaels\Manager:
     *      init(), add(), get(), getAll(), exists(), has(), set(),
     *      remove(), clear(), toJson, isEmpty(), __toString()
     */
    use ManagesItemsTrait;
    use ChainsNestedItemsTrait;

    /**
     * @var array The item collection
     */
    protected $items;
}
