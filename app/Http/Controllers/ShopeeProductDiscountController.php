<?php

namespace App\Http\Controllers;

use App\Jobs\ShopeeDiscountSync;
use App\Models\Shopee;
use App\Models\ShopeeDiscount;
use App\Models\ShopeeSetting;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Requests\DatatableRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;

class ShopeeProductDiscountController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $shops = Shopee::query()
            ->where('seller_id', Auth::id())
            ->orderBy('shop_name', 'asc')
            ->get();

        $data = [
            'shops' => $shops,
        ];
        return view('shopee.product.discount.index', $data);
    }

    public function data(DatatableRequest $request)
    {
        if ($request->ajax()) {
            $websiteId = $request->get('website_id');
            $discountStatus = $request->get('status');

            if($discountStatus == 'all'){
                $shopeeDiscounts = ShopeeDiscount::where('website_id', $websiteId)->get();
            } else {
                $shopeeDiscounts = ShopeeDiscount::where('website_id', $websiteId)
                    ->where('status', $discountStatus)
                    ->get();
            }

            return DataTables::of($shopeeDiscounts)
                ->addColumn('checkbox', function ($shopeeDiscount) {
                    return $shopeeDiscount->id;
                })
                ->addColumn('name', function ($shopeeDiscount) {
                    return $shopeeDiscount->name;
                })
                ->addColumn('status', function ($shopeeDiscount) {
                    return ucfirst($shopeeDiscount->status);
                })
                ->addColumn('start', function ($shopeeDiscount) {
                    return $shopeeDiscount->start;
                })
                ->addColumn('end', function ($shopeeDiscount) {
                    return $shopeeDiscount->end;
                })
                ->addColumn('renew', function ($shopeeDiscount) {
                    $renewable = "<button data-id='".$shopeeDiscount->discount_id."' ";
                    if($shopeeDiscount->renewable == 'no') {
                        $renewable .= "class='btn-action--yellow shopee-discount-renew-btn'>No</button>";
                    } else {
                        $renewable .= "class='btn-action--green shopee-discount-renew-btn'>Yes</button>";
                    }
                    return $renewable;
                })
                ->rawColumns(['checkbox', 'renew','action'])
                ->make(true);
        }

        return false;
    }


    public function sync(Request $request)
    {
        $shopeeSetting = ShopeeSetting::first();
        ShopeeDiscountSync::dispatch($shopeeSetting, $request->shop_id);
        return $this->apiResponse(200,"Sync has started...", $request->shop_id);
    }


    public function manageRenewableDiscounts(Request $request)
    {
        try {
            if ($request->ajax()) {
                $discount = ShopeeDiscount::whereWebsiteId($request->website)
                    ->whereDiscountId($request->id)
                    ->first();
                $message = "";
                if (isset($discount)) {
                    if ($discount->renewable == 'yes') {
                        $discount->renewable = 'no';
                        $message = __("translation.Successfully disabled renew the discount.");
                    } else if ($discount->renewable == 'no') {
                        $discount->renewable = 'yes';
                        $message = __("translation.Successfully enabled renew the discount.");
                    }
                    $discount->save();
                    return response()->json([
                        "success"   => true,
                        "message"   => $message
                    ]);
                }
            }
        } catch (\Exception $exception) {
            Log::debug("Error=" . $exception->getMessage() . "==" . $exception->getFile() . "==" . $exception->getLine());
        }
        return response()->json([
            "success"   => false,
            "message"   => __("translation.Falied to renew the discount.")
        ]);
    }
}
