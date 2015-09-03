<?php
namespace Spider\Integrations\Events;

use League\Event\Emitter;
use League\Event\GeneratorInterface;
use League\Event\ListenerProviderInterface;

/*
 * Interface copied from League\Event\EmitterInterface
 * Only the core needed functionality was kept for this interface
 * To replace standard League Event Dispatcher with your own,
 * implement this interface
 */
interface DispatcherInterface
{
    /**
     * Add a listener for an event.
     *
     * The first parameter should be the event name, and the second should be
     * the event listener. It may be "callable". In this case, the priority emitter also accepts
     * an optional third parameter specifying the priority as an integer.
     *
     * @param string   $event
     * @param callable $listener
     * @param int      $priority
     *
     * @return $this
     */
    public function addListener($event, $listener, $priority = 0);

    /**
     * Remove a specific listener for an event.
     *
     * The first parameter should be the event name, and the second should be
     * the event listener. It may be "callable".
     *
     * @param string   $event
     * @param callable $listener
     *
     * @return $this
     */
    public function removeListener($event, $listener);

    /**
     * Remove all listeners for an event.
     *
     * The first parameter should be the event name. All event listeners will
     * be removed.
     *
     * @param string $event
     *
     * @return $this
     */
    public function removeAllListeners($event);

    /**
     * Check weather an event has listeners.
     *
     * The first parameter should be the event name. We'll return true if the
     * event has one or more registered even listeners, and false otherwise.
     *
     * @param string $event
     *
     * @return bool
     */
    public function hasListeners($event);

    /**
     * Get all the listeners for an event.
     *
     * The first parameter should be the event name. We'll return an array of
     * all the registered even listeners, or an empty array if there are none.
     *
     * @param string $event
     *
     * @return array
     */
    public function getListeners($event);

    /**
     * Emit an event.
     *
     * @param string $event
     *
     * @return EventInterface
     */
    public function emit($event);

    /**
     * Emit a batch of events.
     *
     * @param array $events
     *
     * @return array
     */
    public function emitBatch(array $events);
}
