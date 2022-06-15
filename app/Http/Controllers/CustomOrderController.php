<?php

namespace App\Http\Controllers;

use App\Http\Requests\CustomOrder\DatatableRequest;
use App\Http\Requests\CustomOrder\DeleteRequest;
use App\Http\Requests\CustomOrder\StoreRequest;
use App\Http\Requests\CustomOrder\UpdateRequest;
use App\Models\Channel;
use App\Models\Customer;
use App\Models\CustomOrder;
use App\Models\CustomOrderDetail;
use App\Models\CustomOrderProductImage;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class CustomOrderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $sellerId = Auth::user()->id;

        $orderStatuses = CustomOrder::getAllOrderStatus();

        $orderStatusCountersTotal = 0;
        $orderStatusCounters = [];
        foreach ($orderStatuses as $value => $text) {
            $counter = CustomOrder::where('seller_id', $sellerId)->where('order_status', $value)->count();
            $orderStatusCountersTotal += $counter;

            $orderStatusCounters[] = [
                'id' => $value,
                'text' => $text,
                'total' => $counter
            ];
        }

        $data = [
            'orderStatusCounters' => $orderStatusCounters,
            'orderStatusCountersTotal' => $orderStatusCountersTotal
        ];

        return view('seller.custom-order.index', $data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $sellerId = Auth::user()->id;

        $data = [
            'channels' => Channel::where('seller_id', $sellerId)->where('display_channel', 1)->orderBy('name')->get(),
            'orderStatuses' => CustomOrder::getAllOrderStatus(),
            'paymentStatuses' => CustomOrder::getAllPaymentStatus()
        ];

        return view('seller.custom-order.create', $data);
    }

    /**
     * Store custom_order to the table
     *
     * @param  \App\Http\Requests\CustomOrder\StoreRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreRequest $request)
    {
        try {
            $sellerId = Auth::user()->id;

            $subTotal = 0;
            $inTotal = 0;

            $customer = Customer::firstOrCreate(
                [
                    'customer_name' => $request->customer_name,
                    'contact_phone' => $request->contact_phone,
                    'seller_id' => $sellerId,
                    'order_type' => Customer::ORDER_TYPE_CUSTOM
                ]
            );

            $customOrder = new CustomOrder();
            $customOrder->seller_id = $sellerId;
            $customOrder->shop_id = $request->shop_id;
            $customOrder->channel_id = $request->channel_id;

            // from `customers` table
            $customOrder->customer_id = $customer->id;

            $customOrder->contact_name = $request->contact_name;
            $customOrder->shipping_name = $request->shipping_name;
            $customOrder->shipping_cost = $request->shipping_cost;
            $customOrder->order_status = CustomOrder::ORDER_STATUS_PENDING;
            $customOrder->payment_status = CustomOrder::PAYMENT_STATUS_UNPAID;
            $customOrder->sub_total = $subTotal;
            $customOrder->in_total = $inTotal;
            $customOrder->save();

            $customOrderDetailIds = [];
            foreach ($request->product_name as $idx => $product_name) {
                $customOrderDetail = new CustomOrderDetail();
                $customOrderDetail->custom_order_id = $customOrder->id;
                $customOrderDetail->product_name = $product_name;
                $customOrderDetail->product_description = $request->product_description[$idx] ?? '';
                $customOrderDetail->product_price = $request->product_price[$idx] ?? 0;
                $customOrderDetail->quantity = $request->quantity[$idx] ?? 0;
                $customOrderDetail->seller_id = $sellerId;
                $customOrderDetail->save();

                /**
                 * Sum sub total price * quantity of each product
                 */
                $subTotalProduct = $customOrderDetail->product_price * $customOrderDetail->quantity;
                $subTotal += $subTotalProduct;

                $customOrderDetailIds[$idx] = $customOrderDetail->id;
            }

            /**
             * Update the `sub_total` and `in_total` field
             */
            $inTotal = $subTotal + $customOrder->shipping_cost;

            $customOrderUpdate = CustomOrder::where('id', $customOrder->id)->first();
            $customOrderUpdate->sub_total = $subTotal;
            $customOrderUpdate->in_total = $inTotal;
            $customOrderUpdate->save();


            // Image #1
            if ($request->hasFile('product_image_one')) {
                foreach ($request->file('product_image_one') as $idx => $image_one) {
                    $customOrderProductImage = new CustomOrderProductImage();
                    $customOrderProductImage->custom_order_detail_id = $customOrderDetailIds[$idx] ?? 0;

                    $fileName = Str::uuid() . '.' . $image_one->getClientOriginalExtension();
                    $uploadDirectory = 'uploads/custom-order/products';

                    $image_one->move(public_path($uploadDirectory), $fileName);

                    $customOrderProductImage->image = $uploadDirectory . '/' . $fileName;
                    $customOrderProductImage->save();
                }
            }

            // Image #2
            if ($request->hasFile('product_image_two')) {
                foreach ($request->file('product_image_two') as $idx => $image_two) {
                    $customOrderProductImage = new CustomOrderProductImage();
                    $customOrderProductImage->custom_order_detail_id = $customOrderDetailIds[$idx] ?? 0;

                    $fileName = Str::uuid() . '.' . $image_two->getClientOriginalExtension();
                    $uploadDirectory = 'uploads/custom-order/products';

                    $image_two->move(public_path($uploadDirectory), $fileName);

                    $customOrderProductImage->image = $uploadDirectory . '/' . $fileName;
                    $customOrderProductImage->save();
                }
            }

            // Image #3
            if ($request->hasFile('product_image_three')) {
                foreach ($request->file('product_image_three') as $idx => $image_three) {
                    $customOrderProductImage = new CustomOrderProductImage();
                    $customOrderProductImage->custom_order_detail_id = $customOrderDetailIds[$idx] ?? 0;

                    $fileName = Str::uuid() . '.' . $image_three->getClientOriginalExtension();
                    $uploadDirectory = 'uploads/custom-order/products';

                    $image_three->move(public_path($uploadDirectory), $fileName);

                    $customOrderProductImage->image = $uploadDirectory . '/' . $fileName;
                    $customOrderProductImage->save();
                }
            }

            // Image #4
            if ($request->hasFile('product_image_four')) {
                foreach ($request->file('product_image_four') as $idx => $image_four) {
                    $customOrderProductImage = new CustomOrderProductImage();
                    $customOrderProductImage->custom_order_detail_id = $customOrderDetailIds[$idx] ?? 0;

                    $fileName = Str::uuid() . '.' . $image_four->getClientOriginalExtension();
                    $uploadDirectory = 'uploads/custom-order/products';

                    $image_four->move(public_path($uploadDirectory), $fileName);

                    $customOrderProductImage->image = $uploadDirectory . '/' . $fileName;
                    $customOrderProductImage->save();
                }
            }

            return $this->apiResponse(200, 'Order created.', $customOrder);

        } catch (\Throwable $th) {
            report($th);

            return $this->apiResponse(500, "Something went wrong. {$th->getMessage()}");
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $customOrderId
     * @return \Illuminate\Http\Response
     */
    public function edit($customOrderId)
    {
        $sellerId = Auth::user()->id;

        $customOrder = CustomOrder::where('id', $customOrderId)
                ->with('shop')
                ->with('channel')
                ->with('customer')
                ->withCount('customOrderDetails')
                ->with(['customOrderDetails' => function($customOrderDetail) {
                    $customOrderDetail->with('customOrderProductImages');
                }])
                ->first();

        abort_if(!$customOrder, Response::HTTP_NOT_FOUND, 'Order not found.');

        $data = [
            'channels' => Channel::where('seller_id', $sellerId)->where('display_channel', 1)->orderBy('name')->get(),
            'orderStatuses' => CustomOrder::getAllOrderStatus(),
            'paymentStatuses' => CustomOrder::getAllPaymentStatus(),
            'customOrder' => $customOrder
        ];

        return view('seller.custom-order.edit', $data);
    }

    /**
     * Update the custom_order data.
     *
     * @param  \App\Http\Requests\CustomOrder\UpdateRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateRequest $request)
    {
        try {
            $sellerId = Auth::user()->id;

            $subTotal = 0;
            $inTotal = 0;

            $customer = Customer::firstOrCreate(
                [
                    'customer_name' => $request->customer_name,
                    'contact_phone' => $request->contact_phone,
                    'seller_id' => $sellerId,
                    'order_type' => Customer::ORDER_TYPE_CUSTOM
                ]
            );

            $customOrder = CustomOrder::where('id', $request->id)->first();
            $customOrder->channel_id = $request->channel_id;
            $customOrder->shop_id = $request->shop_id;

            // from `customers` table
            $customOrder->customer_id = $customer->id;

            $customOrder->contact_name = $request->contact_name;
            $customOrder->shipping_name = $request->shipping_name;
            $customOrder->shipping_cost = $request->shipping_cost;
            $customOrder->order_status = $request->order_status;
            $customOrder->payment_status = $request->payment_status;

            foreach ($request->product_id as $idx => $product_id) {
                $customOrderDetail = CustomOrderDetail::where('id', $product_id)->first();
                $customOrderDetail->custom_order_id = $customOrder->id;
                $customOrderDetail->product_name = $request->product_name[$idx] ?? '';
                $customOrderDetail->product_description = $request->product_description[$idx] ?? '';
                $customOrderDetail->product_price = $request->product_price[$idx] ?? 0;
                $customOrderDetail->quantity = $request->quantity[$idx] ?? 0;
                $customOrderDetail->save();

                /**
                 * Sum sub total price * quantity of each product
                 */
                $subTotalProduct = $customOrderDetail->product_price * $customOrderDetail->quantity;
                $subTotal += $subTotalProduct;
            }

            /**
             * Update the `sub_total` and `in_total` field
             */
            $inTotal = $subTotal + $customOrder->shipping_cost;

            $customOrder->sub_total = $subTotal;
            $customOrder->in_total = $inTotal;
            $customOrder->save();

            return $this->apiResponse(200, 'Order updated.');

        } catch (\Throwable $th) {
            report($th);

            return $this->apiResponse(500, "Something went wrong. {$th->getMessage()}");
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Http\Requests\CustomOrder\DeleteRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function destroy(DeleteRequest $request)
    {
        try {
            $customOrder = CustomOrder::where('id', $request->id)->first();
            $customOrder->delete();

            return $this->apiResponse(200, 'Order deleted.');

        } catch (\Throwable $th) {
            report($th);

            return $this->apiResponse(500, "Something went wrong. {$th->getMessage()}");
        }
    }

    /**
     * Server-side datatable for custom-order
     *
     * @param  \App\Http\Requests\CustomOrder\DatatableRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function dataTable(DatatableRequest $request)
    {
        $data = [];

        $sellerId = Auth::user()->id;

        $customOrdersTable = (new CustomOrder())->getTable();
        $customersTable = (new Customer())->getTable();

        $orderStatus = $request->get('orderStatus', 0);

        $start = $request->get('start', 0);
        $limit = $request->get('length', 10);
        $search = isset($request->get('search')['value'])
                ? $request->get('search')['value']
                : null;

        $orderColumnIndex = isset($request->get('order')[0]['column'])
                            ? $request->get('order')[0]['column']
                            : 1;
        $orderDir = isset($request->get('order')[0]['dir'])
                    ? $request->get('order')[0]['dir']
                    : 'desc';

        $availableColumnsOrder = [
            'id', "{$customersTable}.customer_name", 'in_total', 'order_status', 'payment_status', 'created_at'
        ];

        $orderColumnName = isset($availableColumnsOrder[$orderColumnIndex])
                            ? $availableColumnsOrder[$orderColumnIndex]
                            : $availableColumnsOrder[6];

        $customOrders = CustomOrder::selectRaw("{$customOrdersTable}.*, {$customersTable}.customer_name, {$customersTable}.contact_phone AS customer_contact_phone")
                        ->where("{$customOrdersTable}.seller_id", $sellerId)
                        ->with('channel')
                        ->orderStatus($orderStatus)
                        ->searchDataTable($search)
                        ->joinedDataTable()
                        ->orderBy($orderColumnName, $orderDir)
                        ->take($limit)
                        ->skip($start)
                        ->get();

        if (!empty($customOrders)) {
            foreach ($customOrders as $customOrder) {
                $row = [];

                $row[] = $customOrder->id;
                $row[] = '
                    <span class="block whitespace-nowrap mb-1">
                        '. $customOrder->customer_name .'
                    </span>
                    <span class="block whitespace-nowrap mb-1">
                        '. $customOrder->customer_contact_phone .'
                    </span>
                    <span class="whitespace-nowrap px-2 rounded-full bg-yellow-300 text-yellow-900 text-xs">
                        '. Str::lower($customOrder->channel->name) .'
                    </span>
                ';
                $row[] = '
                    <span class="block whitespace-nowrap mb-1">
                        Total Amount: <strong>'. currency_symbol('THB') . number_format($customOrder->in_total, 2) .'</strong>
                    </span>
                    <span class="block whitespace-nowrap mb-1">
                        Shipping Cost: <strong>'. currency_symbol('THB') . number_format($customOrder->shipping_cost, 2) .'</strong>
                    </span>
                ';
                $row[] = $customOrder->str_order_status;

                $paymentStatus = '
                    <span class="whitespace-nowrap px-2 rounded-full bg-green-500 text-green-50 text-xs">
                        '. $customOrder->str_payment_status .'
                    </span>';
                if ($customOrder->payment_status == CustomOrder::PAYMENT_STATUS_UNPAID) {
                    $paymentStatus = '
                        <span class="whitespace-nowrap px-2 rounded-full bg-red-500 text-red-50 text-xs">
                            '. $customOrder->str_payment_status .'
                        </span>';
                }

                $row[] = $paymentStatus;

                $row[] = '
                    <span class="whitespace-nowrap">'. strftime('%e %B %Y', strtotime($customOrder->created_at)) .'</span><br>
                    <span class="whitespace-nowrap">'. strftime('%H:%M', strtotime($customOrder->created_at)) .'</span>
                ';

                $row[] = '
                    <a href="'. route('custom-order.edit', [ 'custom_order' => $customOrder ]) .'"
                        class="btn-action--green"
                        title="Edit">
                        <i class="fas fa-pencil-alt"></i>
                    </a>
                    <button type="button"
                        class="btn-action--red"
                        title="Delete"
                        data-id="'. $customOrder->id .'"
                        onClick="deleteOrder(this)">
                        <i class="fas fa-trash"></i>
                    </button>
                ';

                $data[] = $row;
            }
        }

        $count_total = CustomOrder::where("{$customOrdersTable}.seller_id", $sellerId)->orderStatus($orderStatus)->joinedDataTable()->count();
        $count_total_search = CustomOrder::where("{$customOrdersTable}.seller_id", $sellerId)
                                ->orderStatus($orderStatus)
                                ->searchDataTable($search)
                                ->joinedDataTable()
                                ->count();

        $response = [
            'draw' => $request->get('draw'),
            'recordsTotal' => $count_total,
            'recordsFiltered' => $count_total_search,
            'data' => $data
        ];

        return response()->json($response);
    }
}
