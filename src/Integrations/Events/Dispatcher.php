<?php
namespace Spider\Integrations\Events;

use League\Event\Emitter as LeagueEmitter;

/**
 * Class Emitter
 * @package Spider\Integrations\Events
 */
class Dispatcher extends LeagueEmitter implements DispatcherInterface
{
}
