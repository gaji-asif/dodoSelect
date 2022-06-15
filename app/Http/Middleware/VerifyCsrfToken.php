<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array
     */
    protected $except = [
        '/shopee/order/webhook',
        '/facebook/webhook',
        '/line/webhook',
        '/line/webhook/ac-sale-notify',
        '/line/webhook/tpk-sale-notify'
    ];
}
