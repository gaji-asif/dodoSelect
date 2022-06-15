<?php

namespace App\Observers;

use App\Models\OrderManagement;
use App\Models\WooOrderPurchase;
use App\Models\Shipment;

class ShipmentObserver
{
    /**
     * Handle the Shipment "created" event.
     *
     * @param Shipment $shipment
     * @return void
     */
    public function created(Shipment $shipment)
    {
        //
    }

    /**
     * Handle the Shipment "updated" event.
     *
     * @param Shipment $shipment
     * @return void
     */
    public function updated(Shipment $shipment)
    {
        $orderId = $shipment->order_id; 
        if ($shipment->pack_status === Shipment::PACK_STATUS_PACKED) {
            if($shipment->shipment_for==Shipment::SHIPMENT_FOR_WOO){
                //$wooOrderPurchase = WooOrderPurchase::where('order_id', $orderId)->first();
                //$wooOrderPurchase->save();
            }else{
                $orderManagement = OrderManagement::where('id', $orderId)->first();
                $orderManagement->order_status = OrderManagement::ORDER_STATUS_SHIPPED;
                $orderManagement->save();
            }
        }
    }

    /**
     * Handle the Shipment "deleted" event.
     *
     * @param Shipment $shipment
     * @return void
     */
    public function deleted(Shipment $shipment)
    {
        $orderId = $shipment->order_id;

        if($shipment->shipment_for==Shipment::SHIPMENT_FOR_WOO){
            $wooOrderPurchase = WooOrderPurchase::where('order_id', $orderId)->first();
            $wooOrderPurchase->status = WooOrderPurchase::ORDER_STATUS_PROCESSING;
            $wooOrderPurchase->save();
        }else{
            $orderManagement = OrderManagement::where('id', $orderId)->first();
            $orderManagement->order_status = OrderManagement::ORDER_STATUS_PROCESSING;
            $orderManagement->save();
        }
        
    }

    /**
     * Handle the Shipment "restored" event.
     *
     * @param Shipment $shipment
     * @return void
     */
    public function restored(Shipment $shipment)
    {
        //
    }

    /**
     * Handle the Shipment "force deleted" event.
     *
     * @param Shipment $shipment
     * @return void
     */
    public function forceDeleted(Shipment $shipment)
    {
        //
    }
}
