<?php

namespace App\Utilities;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class QueueJobStatusUtil
{
    private $className;

    /**
     * Create new instance.
     *
     * @param  mixed  $queueJobClassname
     */
    public function __construct($queueJobClassname)
    {
        $this->className = $queueJobClassname;
    }

    /**
     * Get only the classname, ignore the namespace.
     *
     * @return string
     */
    public function parseClassName()
    {
        $explodedClassName = explode('\\', $this->className);
        return Str::slug(end($explodedClassName), '_');
    }

    /**
     * Get the cache key for the job.
     *
     * @return string
     */
    public function getCacheKey()
    {
        return $this->parseClassName() . '_job_status';
    }

    /**
     * Set status
     *
     * @return void
     */
    public function setProcessing(bool $isProcessing)
    {
        $twentyFourHourInSeconds = 86000;

        Cache::put($this->getCacheKey(), $isProcessing, $twentyFourHourInSeconds);
    }

    /**
     * Get status of job
     *
     * @return array
     */
    public function getStatus()
    {
        return [
            'job_name' => $this->getCacheKey(),
            'is_processing' => Cache::get($this->getCacheKey())
        ];
    }
}