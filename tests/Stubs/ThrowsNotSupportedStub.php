<?php
namespace Spider\Test\Stubs;
use Michaels\Manager\Manager;
use Spider\Base\ConfigurableTrait;
use Spider\Base\ThrowsNotSupportedTrait;

/**
 * Class ThrowsNotSupportedStub
 * @package Spider\Test\Stubs
 */
class ThrowsNotSupportedStub {
    use ConfigurableTrait, ThrowsNotSupportedTrait;

    public function __construct( Manager $config = null)
    {
        $this->setConfigManager($config);
    }

    public function thisIsNotSupported()
    {
        $this->notSupported("My test message");
        return true;
    }
}
