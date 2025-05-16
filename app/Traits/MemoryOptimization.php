<?php

namespace App\Traits;

trait MemoryOptimization
{
    protected function freeMemory()
    {
        if (memory_get_usage(true) > 500 * 1024 * 1024) { // Si supera 500MB
            gc_collect_cycles();
            if (function_exists('opcache_reset')) {
                opcache_reset();
            }
        }
    }
}
