<?php

namespace Shopee\Nodes\Order\Parameters;

use Shopee\RequestParameters;

class GetIncomeOfOrder extends RequestParameters
{
    protected $parameters = [
        'ordersn' => ''
    ];


    public function getOrdersn()
    {
        return $this->parameters['ordersn'] ?? '';
    }


    public function setOrdersn(string $ordersn)
    {
        $this->parameters['ordersn'] = $ordersn;

        return $this;
    }
}