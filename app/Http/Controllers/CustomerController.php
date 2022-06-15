<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomOrder;
use App\Models\OrderManagement;
use App\Models\OrderManagementDetail;
use App\Models\Shipment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $customers  = Customer::all();
        return view('customer.index', compact('customers'));
    }

    public function data(Request $request)
    {
        if ($request->ajax()) {

            if (isset($request->id) && $request->id != null) {
                $data = Customer::where([
                    'id' => $request->id
                ])->first();

                return view('elements.form-update-customer', compact(['data']));
            }

            $data = Customer::all();

            $table = Datatables::of($data)
                ->addColumn('last_order', function ($row) {

                    $lastOrder = OrderManagement::where('customer_id', $row->id)->latest()->first();
                    $lastCustomOrders = CustomOrder::where('customer_id', $row->id)->latest()->first();

                    if ($lastOrder && $lastCustomOrders) {
                        if ($lastOrder->created_at > $lastCustomOrders->created_at)
                            return $lastOrder->created_at->format('d F, Y');
                        else
                            return $lastCustomOrders->created_at->format('d F, Y');
                    }
                    elseif ($lastOrder)
                        return $lastOrder->created_at->format('d F, Y');

                    elseif ($lastCustomOrders)
                        return $lastCustomOrders->created_at->format('d F, Y');
                    else
                        return '-';
                })
                ->addColumn('total_orders', function ($row) {

                    $orders = OrderManagement::where('customer_id', $row->id)->get();
                    $customOrders = CustomOrder::where('customer_id', $row->id)->get();

                    $orderCount = $orders->count() + $customOrders->count();
                    $ordersAmount = $orders->sum('in_total') + $customOrders->sum('in_total');

                    if ($orderCount > 0)
                        return
                            '<div>
                                <span class="text-gray-600">
                                    '. __('translation.Order Qty') .'
                                </span>
                                <span>
                                     : '. number_format($orderCount, 0) .'
                                </span>
                            </div>
                            <div>
                                <span class="text-gray-600">
                                    '. __('translation.Total Amount') .'
                                </span>
                                <span>
                                    : '. currency_symbol('THB') . currency_number($ordersAmount, 3) .'
                                </span>
                            </div>';

                    return '-';
                })
                ->addColumn('action', function ($row) {
                    return ' <div class="w-full text-center">
                                <a href="'.route('customer.order_list', [ 'id' => $row->id ]) .'" class="btn-action--blue" title="'. __('translation.Order List') .'">
                                    &nbsp;<i class="fab fa-buffer"></i>&nbsp;
                                </a>
                                <button type="button" class="modal-open btn-action--green" title="'. __('translation.Edit') .'" x-on:click="showEditModal=true" data-id="' . $row->id . '" id="BtnUpdate">
                                    <i class="fas fa-pencil-alt"></i>
                                </button>
                                <button type="button" class="btn-action--red" title="'. __('translation.Delete') .'" data-id="' . $row->id . '" id="BtnDelete">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>';
                })
                ->rawColumns(['last_order', 'total_orders', 'action'])
                ->make(true);
            return $table;
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'customer_name' => 'required|min:4',
            'contact_phone' => 'required|unique:customers,contact_phone',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $customer = Customer::create([
            'customer_name' => $request->customer_name,
            'contact_phone' => $request->contact_phone,
            'seller_id' => Auth::id(),
        ]);

        if ($customer)
            return redirect()->back()->with('success', 'Customer successfully created');
        else
            return redirect()->back()->with('danger', 'Customer addition unsuccessful');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'customer_name' => 'required|min:4',
            'contact_phone' => 'required|unique:App\Models\Customer,contact_phone,' .$request->id,
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $customer = Customer::find($request->id);
        $customer->customer_name = $request->customer_name;
        $customer->contact_phone = $request->contact_phone;
        $result = $customer->update();

        if ($result)
            return redirect()->back()->with('success', 'Customer successfully updated');
        else
            return redirect()->back()->with('danger', 'Customer update unsuccessful');
    }

    public function delete(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 0,
                'message' => 'Field id is required'
            ]);
        }

        Customer::where('id', $request->id)->delete();
        return [
            'status' => 1
        ];
    }

    /**
     * Show order list of `customer` data
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function orderList($id)
    {
        $customer = Customer::findOrFail($id);

        $orderCount = OrderManagement::where('customer_id', $id)
            ->where('seller_id', '=', Auth::user()->id)
            ->count();

        $data = [
            'customer' => $customer,
            'orderCount' => $orderCount
        ];

        return view('customer.order-list', $data);
    }

    public function orderListData(Request $request)
    {
        $data = [];

        $customerId = $request->get('customerId', 0);

        $start = $request->get('start', 0);
        $limit = $request->get('length', 10);
        $search = isset($request->get('search')['value'])
            ? $request->get('search')['value']
            : null;

        $orderColumnIndex = isset($request->get('order')[0]['column'])
            ? $request->get('order')[0]['column']
            : 2;
        $orderDir = isset($request->get('order')[0]['dir'])
            ? $request->get('order')[0]['dir']
            : 'desc';

        $availableColumnsOrder = [
            'id', 'created_at', 'quantity', 'in_total', 'order_status'
        ];

        $orderColumnName = isset($availableColumnsOrder[$orderColumnIndex])
            ? $availableColumnsOrder[$orderColumnIndex]
            : $availableColumnsOrder[1];

        $fields = OrderManagement::where('customer_id', $customerId)
            ->with('customer')
            ->searchDataTable($search)
            ->orderBy($orderColumnName, $orderDir)
            ->quantity()
            ->take($limit)
            ->skip($start)
            ->get();

        if (!empty($fields)) {
            foreach ($fields as $field) {
                $row = [];

                $row[] = ' <a href="'. route('order_management.edit', [ 'order_management' => $field ]) .'" data-id="'.$field->id.'" order-status-id="'.$field->order_status.'" class="cursor-pointer underline" title="Edit">
                                <span class="font-bold text-gray-400">#</span>
                                <span class="relative -left-1 text-blue-500 font-bold">
                                    '. $field->id .'
                                </span>
                            </a>
                            ';

                $dateStr = '';
                if ($field->created_at != null) {
                    $dateStr = $field->created_at->format('d-m-Y') . ' ' . $field->created_at->format('h:i A');
                }
                $row[] = $dateStr;

                $row[] = '<a data-order-id="' . $field->id . '" class="modal-open cursor-pointer" onClick="productsOrdered(this)">' . number_format($field->quantity) .' Item/s</a>';
                $row[] = currency_symbol('THB') . ' ' . number_format($field->in_total) ;

                $orderStatus = strtolower(OrderManagement::getOrderStatus($field->order_status));
                $row[] = ucwords($orderStatus, " ");

                $data[] = $row;
            }
        }

        $count_total = OrderManagement::where('customer_id', $customerId)->count();
        $count_total_search = OrderManagement::where('customer_id', $customerId)->searchDataTable($search)->count();

        $response = [
            'draw' => $request->get('draw'),
            'recordsTotal' => $count_total,
            'recordsFiltered' => $count_total_search,
            'data' => $data
        ];

        return response()->json($response);
    }

    /**
     * Show custom order list of `customer` data
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function customOrderList($id)
    {
        $customer = Customer::findOrFail($id);

        $orderCount = CustomOrder::where('customer_id', $id)
            ->where('seller_id', '=', Auth::user()->id)
            ->count();

        $data = [
            'customer' => $customer,
            'customOrderCount' => $orderCount
        ];

        return view('customer.custom-order-list', $data);
    }

    public function customOrderListData(Request $request)
    {
        $data = [];

        $customerId = $request->get('customerId', 0);

        $start = $request->get('start', 0);
        $limit = $request->get('length', 10);
        $search = isset($request->get('search')['value'])
            ? $request->get('search')['value']
            : null;

        $orderColumnIndex = isset($request->get('order')[0]['column'])
            ? $request->get('order')[0]['column']
            : 2;
        $orderDir = isset($request->get('order')[0]['dir'])
            ? $request->get('order')[0]['dir']
            : 'desc';

        $availableColumnsOrder = [
            'id', 'created_at', 'quantity', 'in_total', 'order_status'
        ];

        $orderColumnName = isset($availableColumnsOrder[$orderColumnIndex])
            ? $availableColumnsOrder[$orderColumnIndex]
            : $availableColumnsOrder[1];

        $fields = CustomOrder::where('customer_id', $customerId)
            ->with('customer')
            ->searchDataTable($search)
            ->orderBy($orderColumnName, $orderDir)
            ->quantity()
            ->take($limit)
            ->skip($start)
            ->get();

        if (!empty($fields)) {
            foreach ($fields as $field) {
                $row = [];

                $row[] = $field->id;

                $dateStr = '';
                if ($field->created_at != null) {
                    $dateStr = $field->created_at->format('d F, Y') . '<br>' . $field->created_at->format('H:i');
                }
                $row[] = $dateStr;

                $row[] = number_format($field->quantity);
                $row[] =  currency_symbol('THB') . ' ' . number_format($field->in_total) ;

                $orderStatus = '';
                if ($field->order_status == 1) {
                    $orderStatus = 'Pending';
                }
                elseif ($field->order_status == 2) {
                    $orderStatus = 'Processing';
                }
                elseif ($field->order_status == 3) {
                    $orderStatus = 'Ready to Ship';
                }
                elseif ($field->order_status == 4) {
                    $orderStatus = 'Shipped';
                }
                $row[] = $orderStatus;

                $data[] = $row;
            }
        }

        $count_total = CustomOrder::where('customer_id', $customerId)->count();
        $count_total_search = CustomOrder::where('customer_id', $customerId)->searchDataTable($search)->count();

        $response = [
            'draw' => $request->get('draw'),
            'recordsTotal' => $count_total,
            'recordsFiltered' => $count_total_search,
            'data' => $data
        ];

        return response()->json($response);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
