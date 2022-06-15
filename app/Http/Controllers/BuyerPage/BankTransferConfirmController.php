<?php

namespace App\Http\Controllers\BuyerPage;

use App\Http\Controllers\Controller;
use App\Http\Requests\BuyerPage\BankTransferConfirm\StoreRequest;
use App\Models\OrderManagement;
use App\Models\Payment;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class BankTransferConfirmController extends Controller
{
    /**
     * Save the bank transfer receipt
     * to payments table
     *
     * @param  \App\Http\Requests\BuyerPage\BankTransferConfirm\StoreRequest  $request
     * @param  string  $orderId
     * @return \Illuminate\Http\Response
     */
    public function store(StoreRequest $request, $orderId)
    {
        try {
            $orderManagement = OrderManagement::where('order_id', $orderId)->first();

            $orderManagementTable = (new OrderManagement())->getTable();
            $paymentTable = (new Payment())->getTable();

            $paymentSlip = Storage::disk('s3')->put('payment-receipt', $request->payment_receipt, 'public');

            DB::beginTransaction();

            DB::table($paymentTable)
                ->insert([
                    'amount' => $orderManagement->in_total,
                    'order_id' => $orderManagement->id,
                    'payment_date' => date('Y-m-d', strtotime($request->payment_date)),
                    'payment_time' => $request->payment_time,
                    'payment_method' => Payment::PAYMENT_METHOD_BANK_TRANSFER,
                    'is_confirmed' => Payment::IS_CONFIRMED_NO,
                    'payment_slip' => $paymentSlip,
                    'created_at' => new DateTime()
                ]);

            DB::table($orderManagementTable)
                ->where('id', $orderManagement->id)
                ->update([
                    'order_status' => OrderManagement::ORDER_STATUS_PAYMENT_UNCONFIRMED,
                    'updated_at' => new DateTime()
                ]);

            DB::commit();

            return $this->apiResponse(Response::HTTP_OK, 'Data successfully saved.');

        } catch (\Throwable $th) {
            report($th);

            DB::rollBack();

            return $this->apiResponse(Response::HTTP_OK, 'Something went wrong.');
        }
    }
}
