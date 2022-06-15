<?php

namespace App\Observers;

use App\Models\OrderPurchase;
use Illuminate\Support\Facades\Auth;

class OrderPurchaseObserver
{
    /**
     * Handle the OrderPurchase "created" event.
     *
     * @param  \App\Models\OrderPurchase  $orderPurchase
     * @return void
     */
    public function created(OrderPurchase $orderPurchase)
    {
        $purchaseOrder = $orderPurchase->where('id', $orderPurchase->id)->first();
        $purchaseOrder->author_id = Auth::user()->id ?? $orderPurchase->seller_id;
        $purchaseOrder->save();
    }

    /**
     * Handle the OrderPurchase "updated" event.
     *
     * @param  \App\Models\OrderPurchase  $orderPurchase
     * @return void
     */
    public function updated(OrderPurchase $orderPurchase)
    {
        //
    }

    /**
     * Handle the OrderPurchase "deleted" event.
     *
     * @param  \App\Models\OrderPurchase  $orderPurchase
     * @return void
     */
    public function deleted(OrderPurchase $orderPurchase)
    {
        //
    }

    /**
     * Handle the OrderPurchase "restored" event.
     *
     * @param  \App\Models\OrderPurchase  $orderPurchase
     * @return void
     */
    public function restored(OrderPurchase $orderPurchase)
    {
        //
    }

    /**
     * Handle the OrderPurchase "force deleted" event.
     *
     * @param  \App\Models\OrderPurchase  $orderPurchase
     * @return void
     */
    public function forceDeleted(OrderPurchase $orderPurchase)
    {
        //
    }
}
