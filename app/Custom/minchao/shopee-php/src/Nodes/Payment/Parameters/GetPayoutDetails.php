<?php

namespace Shopee\Nodes\Payment\Parameters;

use Shopee\Nodes\Payment\Parameters\Traits\BaseParams;
use Shopee\RequestParameters;

class GetPayoutDetails extends RequestParameters
{
    use BaseParams;

    protected $parameters = [
        'payout_time_from' => 0,
        'payout_time_to' => 0,
        'pagination_offset' => 0,
        'pagination_entries_per_page' => 100
    ];


    public function getPayoutTimeFrom()
    {
        return $this->parameters['payout_time_from'];
    }


    public function setPayoutTimeFrom(int $timestamp)
    {
        $this->parameters['payout_time_from'] = $timestamp;

        return $this;
    }


    public function getPayoutTimeTo()
    {
        return $this->parameters['payout_time_to'];
    }


    public function setPayoutTimeTo(int $timestamp)
    {
        $this->parameters['payout_time_to'] = $timestamp;

        return $this;
    }
}