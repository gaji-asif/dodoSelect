<x-app-layout>

    @section('title')
        {{ __('translation.TPK Packing Data') }}
    @endsection


    @if(\App\Models\Role::checkRolePermissions('Can access menu: TPK Packing Data'))
        <div class="col-span-12">
            <x-card.card-default>
                <x-card.header>
                    <x-card.title>
                        {{ __('translation.TPK Packing Data') }}
                    </x-card.title>
                </x-card.header>
                <x-card.body>
                    <div class="mb-6">
                        <x-button type="button" color="red" id="__btnBulkDeleteTpkPackingData" disabled="true">
                            <i class="bi bi-x"></i>
                            <span class="ml-2 mr-1">
                                {{ __('translation.Bulk Delete') }}
                            </span>
                            (<span id="__totalSelectedRows">0</span>)
                        </x-button>
                    </div>
                    <div class="w-full overflow-x-auto">
                        <table class="w-full" id="__sheetDataTpkTable">
                            <thead>
                                <tr>
                                    <th class="px-2 py-4 bg-blue-500 text-white">
                                        #
                                    </th>
                                    <th class="px-2 py-4 bg-blue-500 text-white">
                                        {{ __('translation.Sheet Name') }}
                                    </th>
                                    <th class="px-2 py-4 bg-blue-500 text-white">
                                        {{ __('translation.Date') }} / <br>
                                        {{ __('translation.Amount') }}
                                    </th>
                                    <th class="px-2 py-4 bg-blue-500 text-white">
                                        {{ __('translation.More') }}
                                    </th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </x-card.body>
            </x-card.card-default>
        </div>

        <x-modal.modal-small id="__modalDeleteSheetDataTpk">
            <x-modal.header>
                <x-modal.title>
                    {{ __('translation.Delete Data') }}
                </x-modal.title>
            </x-modal.header>
            <x-modal.body>

                <p class="mb-4 text-center">
                    {{ __('translation.Are you sure to delete the selected data') . '?' }}
                </p>

                <form action="#" method="POST" id="__formDeleteSheetDataTpk">
                    @csrf

                    <div class="flex flex-row items-center justify-center gap-x-2">
                        <x-button type="reset" color="gray" id="__btnCancelDeleteSheetDataTpk">
                            {{ __('translation.No, Close') }}
                        </x-button>
                        <x-button type="submit" color="red" id="__btnSubmitDeleteSheetDataTpk">
                            {{ __('translation.Yes, Delete') }}
                        </x-button>
                    </div>
                </form>
            </x-modal.body>
        </x-modal.modal-small>

    @endif


    @push('top_css')
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.2.2/dist/sweetalert2.min.css">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/jquery-datatables-checkboxes@1.2.12/css/dataTables.checkboxes.css">
    @endpush

    @push('bottom_js')
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.2.2/dist/sweetalert2.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/jquery-datatables-checkboxes@1.2.12/js/dataTables.checkboxes.min.js"></script>
        <script src="{{ asset('pages/seller/sheet-data-tpks/index/table.js?_=' . rand()) }}"></script>
    @endpush

</x-app-layout>
