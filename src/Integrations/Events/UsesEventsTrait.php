<?php
namespace Spider\Integrations\Events;

trait UsesEventsTrait
{
    /* Account for not using dispatcher/ $dispatcher = null */
    protected $dispatcher;

    /**
     * Returns the current Event Dispatcher
     * @return mixed
     */
    public function getDispatcher()
    {
        return $this->dispatcher;
    }

    /**
     * Sets the Event Dispatcher
     * @param DispatcherInterface $dispatcher
     */
    public function setDispatcher(DispatcherInterface $dispatcher = null)
    {
        $this->dispatcher = $dispatcher;
    }

    public function emit($event)
    {
        if (is_null($this->dispatcher)) {
            return null;
        }

        return $this->dispatcher->emit($event);
    }
}
