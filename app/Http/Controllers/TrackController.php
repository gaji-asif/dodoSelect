<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Validator;
use App\Models\Order;

class TrackController extends Controller
{
    public function index()
    {
        // return view('track');
        return redirect('/signin');
    }

    public function getData(Request $request)
    {
        $orders = Order::join('shippers','orders.shipper_id','=','shippers.id')
        ->select(
            'orders.date',
            'orders.time',
            'orders.buyer',
            'orders.date',
            'orders.time',
            'orders.tracking_id',
            'shippers.name as shipper',
            'orders.phone'
        )
        ->where([
            'orders.shop_id' => $request->shop_id,
            ['buyer','like','%'.$request->name.'%'],
        ])
        ->where(DB::raw("substr(phone, -4)"), '=', $request->phone)
        ->get();

        if (count($orders) > 0) {

            $html = (string) View::make('elements.track-table', compact(['orders']));
            return [
                'status' => 1,
                'html' => $html
            ];

        } else {

            return [
                'status' => 0,
                'message' => 'Order not found!'
            ];

        }
    }
}
