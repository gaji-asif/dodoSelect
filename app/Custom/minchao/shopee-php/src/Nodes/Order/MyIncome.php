<?php

namespace Shopee\Nodes\Order;

use Shopee\Nodes\NodeAbstract;
use Shopee\Nodes\Order\Parameters\GetIncomeOfOrder;
use Shopee\ResponseData;

class MyIncome extends NodeAbstract
{
    /**
     * Get the translation list
     *
     * @param  GetIncomeOfOrder  $params
     * @return ResponseData
     */
    public function getIncomeOfOrder($params): ResponseData
    {
        return $this->post('/api/v1/orders/income', $params);
    }
}