<?php

namespace Shopee\Nodes\Payment;

use Shopee\Nodes\NodeAbstract;
use Shopee\Nodes\Payment\Parameters\GetPayoutDetails;
use Shopee\Nodes\Payment\Parameters\GetTransactionList;
use Shopee\ResponseData;

class Payment extends NodeAbstract
{
    /**
     * Get the translation list
     *
     * @param  GetTransactionList  $params
     * @return ResponseData
     */
    public function getTransactionList($params): ResponseData
    {
        return $this->post('/api/v1/wallet/transaction/list', $params);
    }

    /**
     * Get payout detals
     *
     * @param  GetPayoutDetails  $params
     * @return ResponseData
     */
    public function getPayoutDetails($params = []): ResponseData
    {
        return $this->post('/api/v1/payment/get_payout_details', $params);
    }
}