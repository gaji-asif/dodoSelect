<?php

namespace Shopee\Nodes\Order\Parameters;

use Shopee\Nodes\Order\Parameters\Traits\BaseParams;
use Shopee\RequestParameters;

class GetOrderList extends RequestParameters
{
    use BaseParams;

    /**
     * Set default parameters.
     *
     * @var array
     */
    protected $parameters = [
        'pagination_offset' => 0,
        'pagination_entries_per_page' => 100
    ];

    /**
     * Get `create_time_from` parameters
     *
     * @return int
     */
    public function getCreateTimeFrom()
    {
        return $this->parameters['create_time_from'] ?? 0;
    }

    /**
     * Set `create_time_from` parameters
     *
     * @param  int  $timestamp
     * @return self
     */
    public function setCreateTimeFrom(int $timestamp)
    {
        $this->parameters['create_time_from'] = $timestamp;

        return $this;
    }

    /**
     * Get `create_time_to` parameters
     *
     * @return int
     */
    public function getCreateTimeTo()
    {
        return $this->parameters['create_time_to'] ?? 0;
    }

    /**
     * Set `create_time_to` parameters
     *
     * @param  int  $timestamp
     * @return self
     */
    public function setCreateTimeTo(int $timestamp)
    {
        $this->parameters['create_time_to'] = $timestamp;

        return $this;
    }

    /**
     * Get `update_time_from` parameters
     *
     * @return int
     */
    public function getUpdateTimeFrom()
    {
        return $this->parameters['update_time_from'] ?? 0;
    }

    /**
     * Set `update_time_from` parameters
     *
     * @param  int  $timestamp
     * @return self
     */
    public function setUpdateTimeFrom(int $timestamp)
    {
        $this->parameters['update_time_from'] = $timestamp;

        return $this;
    }

    /**
     * Get `update_time_to` parameters
     *
     * @return int
     */
    public function getUpdateTimeTo()
    {
        return $this->parameters['update_time_to'] ?? 0;
    }

    /**
     * Set `update_time_to` parameters
     *
     * @param  int  $timestamp
     * @return self
     */
    public function setUpdateTimeTo(int $timestamp)
    {
        $this->parameters['update_time_to'] = $timestamp;

        return $this;
    }
}