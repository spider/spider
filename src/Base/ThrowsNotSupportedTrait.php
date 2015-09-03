<?php
namespace Spider\Base;

use Spider\Exceptions\NotSupportedException;

trait ThrowsNotSupportedTrait
{
    public function notSupported($message = 'This feature is not currently supported')
    {
        $config = (!is_null($this->config()) && $this->config()->has('errors'))
            ? $this->config()->get('errors')
            : 'fail';

        $handle = strtolower((is_string($config)) ? $config : $config['not_supported']);

        switch ($handle) {
            case 'quiet':
                trigger_error($message, E_USER_WARNING);
                break;

            case 'silent':
                // Not sure why anyone would want this without a logger set up.
                // Or really at all, this seems dangerous.
                break;

            case 'fatal':
                throw new NotSupportedException($message);
                break;

            default:
                throw new NotSupportedException("$message. Also, please check your error handling for Spider. `$handle` is not an accepted value.`");
                break;
        }
    }
}
