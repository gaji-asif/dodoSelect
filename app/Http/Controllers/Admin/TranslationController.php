<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Translation\DeleteRequest;
use App\Http\Requests\Admin\Translation\UpdateRequest;
use App\Http\Requests\DatatableRequest;
use App\Models\Translation;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Yajra\DataTables\Facades\DataTables;

class TranslationController extends Controller
{
    /**
     * Show datatable of translations data
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('admin.translation');
    }

    /**
     * Handle server side datatable
     *
     * @param  \App\Http\Requests\DatatableRequest  $request
     * @return mixed
     */
    public function dataTable(DatatableRequest $request)
    {
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
            'keyword', 'lang_en', 'lang_th'
        ];

        $orderColumnName = isset($availableColumnsOrder[$orderColumnIndex])
                            ? $availableColumnsOrder[$orderColumnIndex]
                            : $availableColumnsOrder[0];

        $translations = Translation::query()
                        ->searchDataTable($search)
                        ->orderBy($orderColumnName, $orderDir);

        return DataTables::of($translations)
                ->addColumn('action', function($translation) {
                    return '
                        <button type="button" class="btn-action--green"
                            data-detail-url="'. route('translation.show', [ 'id' => $translation->id ]) .'"
                            onClick="editTranslation(this)">
                            Edit
                        </button>
                    ';
                })
                ->rawColumns([ 'action' ])
                ->make(true);
    }

    /**
     * Fetch data by id
     *
     * @param  int  $translationId
     * @return \Illuminate\Http\Response
     */
    public function show($translationId)
    {
        $translation = Translation::findOrFail($translationId);

        abort_if(!$translation, 'Data not found');

        return $this->apiResponse(Response::HTTP_OK, 'Success', [
            'translation' => $translation
        ]);
    }

    /**
     * Scan translation code in the entire project
     *
     * @return \Illuminate\Http\Response
     */
    public function store()
    {
        try {
            Artisan::call('dodo:scan-translation-word');

            return $this->apiResponse(Response::HTTP_OK, 'We are scanning the entire project in the background');

        } catch (\Throwable $th) {
            report($th);

            return $this->apiResponse(Response::HTTP_INTERNAL_SERVER_ERROR, 'Something went wrong');
        }
    }

    /**
     * Update the data
     *
     * @param \App\Http\Requests\Admin\Translation\UpdateRequest $request
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateRequest $request) {
        try {
            $translation = Translation::where('id', $request->id)->first();
            $translation->lang_en = $request->lang_en;
            $translation->lang_th = $request->lang_th;
            $translation->save();

            Cache::forget('translations');

            return $this->apiResponse(Response::HTTP_OK, 'Translation data successfully updated');

        } catch (\Throwable $th) {
            report($th);

            return $this->apiResponse(Response::HTTP_INTERNAL_SERVER_ERROR, 'Something went wrong');
        }
    }

    /**
     * Bulk delete translation data
     *
     * @param  \App\Http\Requests\Admin\Translation\DeleteRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function delete(DeleteRequest $request)
    {
        try {
            $translationIds = $request->ids;
            Translation::destroy($translationIds);

            return $this->apiResponse(Response::HTTP_OK, 'Data successfully deleted.');

        } catch (\Throwable $th) {
            report($th);

            return $this->apiResponse(Response::HTTP_INTERNAL_SERVER_ERROR, 'Something went wrong.');
        }
    }
}
