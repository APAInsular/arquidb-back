<?php

namespace App\Traits;

trait ImportTracker
{
    protected $processed = 0;
    protected $successful = 0;
    protected $failed = 0;

    public function incrementProcessed()
    {
        $this->processed++;
    }

    public function incrementSuccessful()
    {
        $this->successful++;
    }

    public function incrementFailed()
    {
        $this->failed++;
    }

    public function getRowCount()
    {
        return $this->processed;
    }

    public function getSuccessCount()
    {
        return $this->successful;
    }

    public function getErrorCount()
    {
        return $this->failed;
    }

    public function resetCounters()
    {
        $this->processed = 0;
        $this->successful = 0;
        $this->failed = 0;
    }
}
