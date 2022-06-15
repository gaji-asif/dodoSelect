<?php

namespace App\Helper;

class Rescue
{
    
    /**
 * Catch a potential exception and return a default value.
 *
 * @param  callable  $callback
 * @param  mixed  $rescue
 * @param  bool  $report
 * @return mixed
 */
function rescue(callable $callback, $rescue = null, $report = true)
{
    try {
        return $callback();
    } catch (Throwable $e) {
        if ($report) {
            report($e);
        }

        return $rescue instanceof Closure ? $rescue($e) : $rescue;
    }
}

}
