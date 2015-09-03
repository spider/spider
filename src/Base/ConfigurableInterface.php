<?php
namespace Spider\Base;

use Michaels\Manager\Manager;

interface ConfigurableInterface
{
    /**
     * Returns the configuration for this instance.
     *
     * Only returns the Manager itself
     * @return Manager
     */
    public function config();

    /**
     * Sets the configuration manager.
     *
     * @param Manager $manager
     * @return mixed
     */
    public function setConfigManager($manager);
}
