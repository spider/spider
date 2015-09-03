<?php
namespace Spider\Base;

use Michaels\Manager\Manager;

trait ConfigurableTrait
{
    /** @var  Manager Configuration Manager */
    protected $configManager;

    /**
     * Returns the configuration for this instance.
     *
     * Only returns the Manager itself
     * @return Manager
     */
    public function config()
    {
        return $this->configManager;
    }

    /**
     * Sets the configuration manager.
     *
     * @param Manager $manager
     * @return mixed
     */
    public function setConfigManager($manager = null)
    {
        if ($manager instanceof Manager) {
            $this->configManager = $manager;
        } else {
            $this->configManager = new Manager((is_array($manager) ? $manager : []));
        }
    }
}
