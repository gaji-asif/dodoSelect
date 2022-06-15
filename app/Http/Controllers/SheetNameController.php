<?php

namespace App\Http\Controllers;

use App\Enums\SheetNameSyncStatusEnum;
use App\Http\Requests\DatatableRequest;
use App\Http\Requests\SheetName\SheetNameDeleteRequest;
use App\Http\Requests\SheetName\SheetNameStoreRequest;
use App\Http\Requests\SheetName\SheetNameUpdateRequest;
use App\Models\SheetDoc;
use App\Models\SheetName;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\DataTables;

class SheetNameController extends Controller
{
    /**
     * Display datatable of the sheet names.
     *
     * @param  int  $sheetDocId
     * @return \Illuminate\Http\Response
     */
    public function index(int $sheetDocId)
    {
        $sellerId = Auth::user()->id;

        $sheetDoc = SheetDoc::query()
            ->where('seller_id', $sellerId)
            ->where('id', $sheetDocId)
            ->firstOrFail();

        return view('sheet-names.index', compact('sheetDoc'));
    }

    /**
     * Handle server side datatable of sheet names data
     *
     * @param  \App\Http\Requests\DatatableRequest  $request
     * @param  int  $sheetDocId
     * @return DataTables
     */
    public function datatable(DatatableRequest $request, int $sheetDocId)
    {
        $sellerId = Auth::user()->id;

        $search = isset($request->get('search')['value'])
            ? $request->get('search')['value']
            : null;

        $orderColumnList = [
            'sheet_name', 'sheet_name', 'allow_to_sync', 'last_sync', 'sync_status'
        ];

        $orderColumnIndex = isset($request->get('order')[0]['column'])
            ? $request->get('order')[0]['column']
            : 0;

        $orderColumnDir = isset($request->get('order')[0]['dir'])
            ? $request->get('order')[0]['dir']
            : 'desc';

        $orderColumnName = $orderColumnList[$orderColumnIndex] ?? 'file_name';

        $sheetNames = SheetName::query()
            ->where('seller_id', $sellerId)
            ->where('sheet_doc_id', $sheetDocId)
            ->searchTable($search)
            ->orderBy($orderColumnName, $orderColumnDir);

        return DataTables::of($sheetNames)
                ->addColumn('str_allow_to_sync', function ($sheetName) {
                    return $sheetName->allow_to_sync
                        ? '
                            <span class="badge-status--green">
                                '. __('translation.Yes') .'
                            </span>
                        '
                        : '
                            <span class="badge-status--red">
                                '. __('translation.No') .'
                            </span>
                        ';
                })
                ->addColumn('str_last_sync', function ($sheetName) {
                    return !empty($sheetName->last_sync) ? $sheetName->last_sync->format('d M Y H:i') : '-';
                })
                ->addColumn('str_sync_status', function ($sheetName) {
                    return $sheetName->sync_status->label;
                })
                ->addColumn('actions', function ($sheetName) {
                    $syncNowButton = '
                        <button class="btn-action--blue"
                            title="'. __('translation.Sync Now') .'"
                            data-id="'. $sheetName->id .'"
                            data-sheet-name="'. $sheetName->sheet_name .'"
                            onClick="syncNowSheetName(this)">
                            <i class="bi bi-arrow-repeat"></i>
                        </button>';

                    if ($sheetName->sync_status->equals(SheetNameSyncStatusEnum::syncing())) {
                        $syncNowButton = '
                            <button class="btn-action--blue"
                                title="'. __('translation.Sync Now') .'"
                                disabled="true">
                                <i class="bi bi-arrow-repeat"></i>
                            </button>';
                    }

                    return $syncNowButton . '
                        <button class="btn-action--yellow"
                            title="'. __('translation.Edit') .'"
                            data-id="'. $sheetName->id .'"
                            onClick="editSheetName(this)">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button class="btn-action--red"
                            title="'. __('translation.Delete') .'"
                            data-id="'. $sheetName->id .'"
                            onClick="deleteSheetName(this)">
                            <i class="bi bi-trash"></i>
                        </button>
                    ';
                })
                ->addIndexColumn()
                ->rawColumns(['str_allow_to_sync', 'str_last_sync', 'str_sync_status', 'actions'])
                ->make(true);
    }

    /**
     * Store a newly created sheet name.
     *
     * @param  \App\Http\Requests\SheetName\SheetNameStoreRequest  $request
     * @param  int  $sheetDocId
     * @return \Illuminate\Http\Response
     */
    public function store(SheetNameStoreRequest $request, int $sheetDocId)
    {
        $requestData = $request->validated();

        $sheetName = new SheetName();
        $sheetName->sheet_doc_id = $sheetDocId;
        $sheetName->sheet_name = trim($requestData['sheet_name']);
        $sheetName->allow_to_sync = $requestData['allow_to_sync'];
        $sheetName->seller_id = Auth::user()->id;
        $sheetName->save();

        return $this->apiResponse(Response::HTTP_OK, __('translation.Data has been saved'));
    }

    /**
     * Get the sheet name for edit.
     *
     * @param  int  $sheetDocId
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(int $sheetDocId, int $id)
    {
        $sellerId = Auth::user()->id;

        $sheetName = SheetName::query()
            ->where('seller_id', $sellerId)
            ->where('sheet_doc_id', $sheetDocId)
            ->where('id', $id)
            ->firstOrFail();

        return $this->apiResponse(Response::HTTP_OK, null, [
            'sheet_name' => $sheetName
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\SheetName\SheetNameUpdateRequest  $request
     * @param  int  $sheetDocId
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(SheetNameUpdateRequest $request, int $sheetDocId, int $id)
    {
        $requestData = $request->validated();

        $sellerId = Auth::user()->id;

        $sheetName = SheetName::query()
            ->where('seller_id', $sellerId)
            ->where('id', $id)
            ->firstOrFail();

        $sheetName->sheet_name = trim($requestData['sheet_name']);
        $sheetName->allow_to_sync = $requestData['allow_to_sync'];
        $sheetName->save();

        return $this->apiResponse(Response::HTTP_OK, __('translation.Data has been updated'));
    }

    /**
     * Delete the sheet name data
     *
     * @param  \App\Http\Requests\SheetName\SheetNameDeleteRequest
     * @param  int  $sheetDocId
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function delete(SheetNameDeleteRequest $request, int $sheetDocId, int $id)
    {
        $sellerId = Auth::user()->id;

        $sheetName = SheetName::query()
            ->where('seller_id', $sellerId)
            ->where('id', $id)
            ->first();

        /**
         * Fetch first then delete
         * because of to fired event model
         */
        $sheetName->delete();

        return $this->apiResponse(Response::HTTP_OK, __('translation.Data has been deleted'));
    }
}
