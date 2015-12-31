<?php
namespace Spider\Test\Scenarios;
use Spider\Commands\Bag;

/**
 * Class AbstractScenario
 * @package Spider\Test\Scenarios
 */
abstract class AbstractScenario
{
    protected $bag;
    protected $bagData;
    static protected $description = 'no description provided';

    static public function getDescription()
    {
        return static::$description;
    }

    public function __construct(array $options = null)
    {
        $this->bag = $this->buildBag($options);
    }

    public function __invoke()
    {
        return $this->getCommandBag();
    }

    public function toJson()
    {
        return json_encode($this->bag);
    }

    public function getCommandBag()
    {
        return $this->bag;
    }

    protected function ensureHasId(array $options)
    {
        if (!isset($options['id'])) {
            throw new \Exception("You must supply a native ID to " . get_called_class());
        }
    }

    protected function getData()
    {
        return ['one' => 1, 'two' => 'two', 'three' => false];
    }

    protected function getWheres()
    {
        return [
            ['one', Bag::COMPARATOR_EQUAL, 'one', Bag::CONJUNCTION_AND],
            ['two', Bag::COMPARATOR_GT, 2, Bag::CONJUNCTION_AND],
            ['three', Bag::COMPARATOR_LT, 3.14, Bag::CONJUNCTION_OR],
            ['four', Bag::COMPARATOR_EQUAL, true, Bag::CONJUNCTION_AND]
        ];
    }

    abstract protected function buildBag(array $options = null);
}
