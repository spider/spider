<?php
namespace Spider\Test\Scenarios;
use Spider\Commands\Bag;

/**
 * Base Class for all Testing Scenarios
 * @package Spider\Test\Scenarios
 */
abstract class AbstractScenario
{
    /** @var  Bag Valid Command Bag */
    protected $bag;

    /** @var  array Array representation of the Bag */
    protected $bagData;

    /** @var string Short Description of the scenario (for test names) */
    static protected $description = 'no description provided';

    /**
     * Returns the description of the scenario
     * @return string
     */
    static public function getDescription()
    {
        return static::$description;
    }

    /**
     * Returns the data used as a test for scenarios
     * @return array
     */
    static public function getData()
    {
        return ['one' => 1, 'two' => 'two', 'three' => false];
    }

    /**
     * Returns the "where" constraints used for complex scenarios
     * @return array
     */
    static public function getWheres()
    {
        return [
            ['one', Bag::COMPARATOR_EQUAL, 'one', Bag::CONJUNCTION_AND],
            ['two', Bag::COMPARATOR_GT, 2, Bag::CONJUNCTION_AND],
            ['three', Bag::COMPARATOR_LT, 3.14, Bag::CONJUNCTION_OR],
            ['four', Bag::COMPARATOR_EQUAL, true, Bag::CONJUNCTION_AND]
        ];
    }

    /**
     * AbstractScenario constructor.
     * @param array|null $options
     */
    public function __construct(array $options = null)
    {
        $this->bag = $this->buildBag($options);
    }

    /**
     * If called as callable
     * @return Bag
     */
    public function __invoke()
    {
        return $this->getCommandBag();
    }

    /**
     * Returns the valid Bag for the scenario
     * @return Bag
     */
    public function getCommandBag()
    {
        return $this->bag;
    }

    /**
     * Ensure the $options contains a valid native ID
     * @param array $options
     * @throws \Exception
     */
    protected function ensureHasId(array $options)
    {
        if (!isset($options['id'])) {
            throw new \Exception("You must supply a native ID to " . get_called_class());
        }
    }

    /**
     * Creates and Returns the valid command bag for the scenario
     * Used by getCommandBag()
     * @param array|null $options
     * @return Bag
     */
    abstract protected function buildBag(array $options = null);
}
