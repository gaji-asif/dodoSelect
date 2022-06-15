<x-app-layout>

    @section('title')
        Translation
    @endsection

    @push('top_css')
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/jquery-datatables-checkboxes@1.2.12/css/dataTables.checkboxes.css">
    @endpush

    @push('bottom_css')
        <link rel="stylesheet" href="{{ asset('css/datatable-custom-toolbar.css?_=' . rand()) }}">
    @endpush

    <div class="col-span-12">
        <x-card.card-default>
            <x-card.header>
                <x-card.title>
                    Translation
                </x-card.title>
            </x-card.header>
            <x-card.body>

                <div class="hidden">
                    <x-button type="button" color="yellow" class="__btnWordScan">
                        <i class="bi bi-search"></i>
                        <span class="ml-2">
                            Words Scan
                        </span>
                    </x-button>
                    <x-button type="button" color="red" class="__btnDeleteTranslation" disabled="true">
                        <i class="bi bi-x-lg"></i>
                        <span class="ml-2">
                            Delete (<span class="__totalSelectedRows">0</span>)
                        </span>
                    </x-button>
                </div>

                <x-alert-info id="__alertInfoTable" class="alert hidden"></x-alert-info>
                <x-alert-success id="__alertSuccessTable" class="alert hidden"></x-alert-success>
                <x-alert-danger id="__alertDangerTable" class="alert hidden"></x-alert-danger>

                <div class="w-full overflow-x-auto">
                    <table class="w-full" id="__translationTable">
                        <thead>
                            <tr>
                                <th class="px-4 py-2 bg-blue-500 text-white">&nbsp;</th>
                                <th class="px-4 py-2 bg-blue-500 text-white">
                                    Keyword
                                </th>
                                <th class="px-4 py-2 bg-blue-500 text-white">
                                    English
                                </th>
                                <th class="px-4 py-2 bg-blue-500 text-white">
                                    Thai
                                </th>
                                <th class="px-4 py-2 bg-blue-500 text-white w-20">
                                    ###
                                </th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </x-card.body>
        </x-card.card-default>
    </div>


    <x-modal.modal-small id="__modalEditTranslation">
        <x-modal.header>
            <x-modal.title>
                Edit Translation
            </x-modal.title>
        </x-modal.header>
        <x-modal.body>

            <x-alert-danger id="__alertDangerEditTranslation" class="alert hidden"></x-alert-danger>

            <form action="{{ route('translation.update') }}" method="post" id="__formEditTranslation">
                @csrf
                <input type="hidden" name="id" id="__idEditTranslation">
                <div class="mb-5">
                    <div class="grid grid-cols-1 gap-4">
                        <div>
                            <label class="mb-0">
                                Key <x-form.required-mark/> :
                            </label>
                            <x-input type="text" id="__keyEditTranslation" class="bg-gray-200" readonly />
                        </div>
                        <div>
                            <label class="mb-0">
                                English <x-form.required-mark/> :
                            </label>
                            <x-input type="text" name="lang_en" id="__lang_enEditTranslation" />
                        </div>
                        <div>
                            <label class="mb-0">
                                Thai <x-form.required-mark/> :
                            </label>
                            <x-input type="text" name="lang_th" id="__lang_thEditTranslation" />
                        </div>
                    </div>
                </div>
                <div class="text-center">
                    <x-button type="reset" color="gray" id="__btnCancelEditTranslation">
                        Cancel
                    </x-button>
                    <x-button type="submit" color="blue" id="__btnSubmitEditTranslation">
                        Update Data
                    </x-button>
                </div>
            </form>
        </x-modal.body>
    </x-modal.modal-small>


    <x-modal.modal-small id="__modalDeleteTranslation">
        <x-modal.header>
            <x-modal.title>
                Delete Translation
            </x-modal.title>
        </x-modal.header>
        <x-modal.body>

            <x-alert-danger id="__alertDangerDeleteTranslation" class="alert hidden"></x-alert-danger>

            <div class="mb-4">
                <p class="text-center">
                    Are you sure to delete the selected data?
                </p>
            </div>
            <div class="pb-3 flex flex-row items-center justify-center gap-2">
                <x-button type="button" color="gray" id="__btnCancelDeleteTranslation">
                    No, Close
                </x-button>
                <x-button type="button" color="red" id="__btnYesDeleteTranslation">
                    Yes, Delete
                </x-button>
            </div>
        </x-modal.body>
    </x-modal.modal-small>


    @push('bottom_js')
        <script src="https://cdn.jsdelivr.net/npm/jquery-datatables-checkboxes@1.2.12/js/dataTables.checkboxes.min.js"></script>

        <script>
            const textProcessing = 'Processing';
            const textUpdateData = 'Update Data';
        </script>
        <script src="{{ asset('pages/admin/translation/index/table.js?_=' . rand()) }}"></script>
    @endpush

</x-app-layout>