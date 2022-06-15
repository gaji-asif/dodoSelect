<?php

namespace App\Http\Controllers;

use App\Models\WooCronReport;
use App\Models\WooProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Datatables;



class CronReportController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {

            $data = WooCronReport::join("woo_shops","woo_shops.id",'woo_cron_reports.shop_id')
                ->where("woo_shops.seller_id",Auth::id())
                ->select(
                'woo_cron_reports.*',
                'woo_shops.name',
            );
            $table = Datatables::of($data)

                ->addColumn('checkbox', function ($row) {
                    return $row->website_id.'*'.$row->id.'*'.$row->product_id; //pass website_id and product_id to avoid conflict
                })
                /*
                      ->addColumn('image', function ($row) {
                          $image = '';

                            $images = json_decode($row->images);
                          if(!empty($images)){
                            $image = $images[0]->src;
                            return '<img width="50px" src="'.$image.'">';
                          }else{
                            return $image;
                          }
                      })
                      */


                ->addColumn('price', function ($row) {
                    return $row->price;
                })

                ->editColumn('updated_at', function ($row) {
                    return Date($row->updated_at);
                })

                ->addColumn('status', function ($row) {
                    $status = ucfirst(str_replace("-", " ", $row->status));
                    return $status;
                })

                ->addColumn('manage', function ($row) {
                    return '

          <span x-on:click="showEditModal=true" class="modal-open bg-green-500 text-white rounded px-2 py-1 mr-1 capitalize cursor-pointer BtnUpdate" data-id="'.$row->id.'" ><i class="fas fa-pencil-alt"></i></span>
          <span x-on:click="showEditModal=true" class="modal-open bg-green-500 text-white rounded px-2 py-1 mr-1 capitalize cursor-pointer BtnEditQty" data-product_code="'.$row->product_code.'" ><i class="fas fa-bars"></i></span>
          <span class="bg-red-500 text-white rounded px-2 py-1 mr-1 capitalize cursor-pointer BtnDelete" data-id="'.$row->id.'"><i class="fas fa-trash-alt"></i></span>
          ';
                })
                ->rawColumns(['checkbox','website_id','image','manage'])
                ->make(true);
            return $table;
        }

        $title = 'cronReport';

        return view('cron-reports.index', compact('title'));
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
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
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
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
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
