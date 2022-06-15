<?php

namespace Shopee\Nodes\Payment\Parameters;

use Shopee\Nodes\Payment\Parameters\Traits\BaseParams;
use Shopee\RequestParameters;

class GetTransactionList extends RequestParameters
{
    use BaseParams;

    protected $parameters = [
        'pagination_offset' => 0,
        'pagination_entries_per_page' => 100
    ];


    public function getCreateTimeFrom()
    {
        return $this->parameters['create_time_from'] ?? 0;
    }


    public function setCreateTimeFrom(int $timestamp)
    {
        $this->parameters['create_time_from'] = $timestamp;

        return $this;
    }


    public function getCreateTimeTo()
    {
        return $this->parameters['create_time_to'] ?? 0;
    }


    public function setCreateTimeTo(int $timestamp)
    {
        $this->parameters['create_time_to'] = $timestamp;

        return $this;
    }


    public function getWalletType()
    {
        return $this->parameters['wallet_type'] ?? '';
    }


    public function setWalletType(string $walletType)
    {
        $this->parameters['wallet_type'] = $walletType;

        return $this;
    }


    public function getTransactionType()
    {
        return $this->parameters['transaction_type'] ?? '';
    }


    public function setTransactionType(string $walletType)
    {
        $this->parameters['transaction_type'] = $walletType;

        return $this;
    }
}