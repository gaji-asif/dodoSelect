<?php

namespace App\Http\Controllers;

use App\Http\Requests\DatatableRequest;
use App\Http\Requests\SheetDoc\SheetDocDeleteRequest;
use App\Http\Requests\SheetDoc\SheetDocStoreRequest;
use App\Http\Requests\SheetDoc\SheetDocUpdateRequest;
use App\Models\SheetDoc;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\DataTables;

class SheetDocController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('sheet-docs.index');
    }

    /**
     * Handle server side datatable of sheet docs data
     *
     * @param  \App\Http\Requests\DatatableRequest  $request
     * @return DataTables
     */
    public function datatable(DatatableRequest $request)
    {
        $sellerId = Auth::user()->id;

        $search = isset($request->get('search')['value'])
            ? $request->get('search')['value']
            : null;

        $orderColumnList = [
            'file_name', 'file_name', 'spreadsheet_id'
        ];

        $orderColumnIndex = isset($request->get('order')[0]['column'])
            ? $request->get('order')[0]['column']
            : 0;

        $orderColumnDir = isset($request->get('order')[0]['dir'])
            ? $request->get('order')[0]['dir']
            : 'desc';

        $orderColumnName = $orderColumnList[$orderColumnIndex] ?? 'file_name';

        $sheetDocs = SheetDoc::query()
            ->where('seller_id', $sellerId)
            ->searchTable($search)
            ->orderBy($orderColumnName, $orderColumnDir);

        return DataTables::of($sheetDocs)
                ->addColumn('actions', function ($sheetDoc) {
                    return '
                        <a href="'. route('sheet-names.index', [ 'sheetDoc' => $sheetDoc->id ]) .'" class="btn-action--green"
                            title="'. __('translation.Manage Sheet') .'">
                            <i class="bi bi-pencil-square"></i>
                        </a>
                        <button class="btn-action--yellow"
                            title="'. __('translation.Edit') .'"
                            data-id="'. $sheetDoc->id .'"
                            onClick="editSheetDoc(this)">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button class="btn-action--red"
                            title="'. __('translation.Delete') .'"
                            data-id="'. $sheetDoc->id .'"
                            onClick="deleteSheetDoc(this)">
                            <i class="bi bi-trash"></i>
                        </button>
                    ';
                })
                ->addIndexColumn()
                ->rawColumns(['actions'])
                ->make(true);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\SheetDoc\SheetDocStoreRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(SheetDocStoreRequest $request)
    {
        $requestData = $request->validated();

        $sellerId = Auth::user()->id;

        $sheetDoc = new SheetDoc();
        $sheetDoc->file_name = $requestData['file_name'];
        $sheetDoc->spreadsheet_id = $requestData['spreadsheet_id'];
        $sheetDoc->seller_id = $sellerId;
        $sheetDoc->save();

        return $this->apiResponse(Response::HTTP_OK, __('translation.New document added'));
    }

    /**
     * Show edit form.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $sellerId = Auth::user()->id;

        $sheetDoc = SheetDoc::query()
            ->where('seller_id', $sellerId)
            ->where('id', $id)
            ->firstOrFail();

        return $this->apiResponse(Response::HTTP_OK, null, [ 'sheet_doc' => $sheetDoc ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\SheetDoc\SheetDocUpdateRequest  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(SheetDocUpdateRequest $request, $id)
    {
        $requestData = $request->validated();

        $sheetDoc = SheetDoc::where('id', $id)->first();
        $sheetDoc->file_name = $requestData['file_name'];
        $sheetDoc->spreadsheet_id = $requestData['spreadsheet_id'];
        $sheetDoc->save();

        return $this->apiResponse(Response::HTTP_OK, __('translation.Document has been updated'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Http\Requests\SheetDoc\SheetDocDeleteRequest  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function delete(SheetDocDeleteRequest $request, $id)
    {
        SheetDoc::where('id', $id)->delete();

        return $this->apiResponse(Response::HTTP_OK, __('translation.Document has been deleted'));
    }
}
