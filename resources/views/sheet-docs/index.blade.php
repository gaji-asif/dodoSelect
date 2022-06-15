<x-app-layout>

    @section('title')
        {{ __('translation.TPK Packing Spreadsheet') }}
    @endsection


    @if(\App\Models\Role::checkRolePermissions('Can access menu: TPK Packing Data'))
        <div class="col-span-12">
            <x-card.card-default>
                <x-card.header>
                    <x-card.title>
                        {{ __('translation.TPK Packing Spreadsheet') }}
                    </x-card.title>
                </x-card.header>
                <x-card.body>
                    <div class="flex flex-col items-center justify-center sm:flex-row sm:justify-start">
                        <x-button type="button" color="green" id="__btnAddSheetDoc">
                            <i class="bi bi-plus-circle"></i>
                            <span class="ml-2">
                                {{ __('translation.Add Document') }}
                            </span>
                        </x-button>
                    </div>
                    <div class="mt-8 w-full overflow-x-auto">
                        <table class="w-full" id="__sheetDocsTable">
                            <thead>
                                <tr>
                                    <th class="px-2 py-4 bg-blue-500 text-white">
                                        #
                                    </th>
                                    <th class="px-2 py-4 bg-blue-500 text-white">
                                        {{ __('translation.File Name') }}
                                    </th>
                                    <th class="px-2 py-4 bg-blue-500 text-white">
                                        {{ __('translation.Spreadsheet ID') }}
                                    </th>
                                    <th class="px-2 py-4 bg-blue-500 text-white">
                                        {{ __('translation.actions') }}
                                    </th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </x-card.body>
            </x-card.card-default>
        </div>

        <x-modal.modal-small id="__modalAddSheetDoc">
            <x-modal.header>
                <x-modal.title>
                    {{ __('translation.Add Document') }}
                </x-modal.title>
            </x-modal.header>
            <x-modal.body>

                <form action="#" method="POST" id="__formAddSheetDoc">
                    @csrf

                    <x-form.form-group>
                        <x-form.label for="__fileNameAddSheetDoc">
                            {{ __('translation.File Name') }}
                            <x-form.required-mark />
                        </x-form.label>
                        <x-form.input type="text" name="file_name" id="__fileNameAddSheetDoc" />
                    </x-form.form-group>
                    <x-form.form-group>
                        <x-form.label for="__spreadsheetIdAddSheetDoc">
                            {{ __('translation.Spreadsheet ID') }}
                            <x-form.required-mark />
                        </x-form.label>
                        <x-form.input type="text" name="spreadsheet_id" id="__spreadsheetIdAddSheetDoc" />
                    </x-form.form-group>
                    <div class="flex flex-row items-center justify-center gap-x-2">
                        <x-button type="reset" color="gray" id="__btnCancelAddSheetDoc">
                            {{ __('translation.Cancel') }}
                        </x-button>
                        <x-button type="submit" color="blue" id="__btnSubmitAddSheetDoc">
                            {{ __('translation.Save') }}
                        </x-button>
                    </div>
                </form>
            </x-modal.body>
        </x-modal.modal-small>


        <x-modal.modal-small id="__modalEditSheetDoc">
            <x-modal.header>
                <x-modal.title>
                    {{ __('translation.Edit Document') }}
                </x-modal.title>
            </x-modal.header>
            <x-modal.body>

                <form action="#" method="POST" id="__formEditSheetDoc">
                    @csrf

                    <input type="hidden" name="id" id="__idEditSheetDoc">
                    <x-form.form-group>
                        <x-form.label for="__fileNameEditSheetDoc">
                            {{ __('translation.File Name') }}
                            <x-form.required-mark />
                        </x-form.label>
                        <x-form.input type="text" name="file_name" id="__fileNameEditSheetDoc" />
                    </x-form.form-group>
                    <x-form.form-group>
                        <x-form.label for="__spreadsheetIdEditSheetDoc">
                            {{ __('translation.Spreadsheet ID') }}
                            <x-form.required-mark />
                        </x-form.label>
                        <x-form.input type="text" name="spreadsheet_id" id="__spreadsheetIdEditSheetDoc" />
                    </x-form.form-group>
                    <div class="flex flex-row items-center justify-center gap-x-2">
                        <x-button type="reset" color="gray" id="__btnCancelEditSheetDoc">
                            {{ __('translation.Cancel') }}
                        </x-button>
                        <x-button type="submit" color="blue" id="__btnSubmitEditSheetDoc">
                            {{ __('translation.Save') }}
                        </x-button>
                    </div>
                </form>
            </x-modal.body>
        </x-modal.modal-small>

        <x-modal.modal-small id="__modalDeleteSheetDoc">
            <x-modal.header>
                <x-modal.title>
                    {{ __('translation.Delete Document') }}
                </x-modal.title>
            </x-modal.header>
            <x-modal.body>

                <p class="mb-4 text-center">
                    {{ __('translation.Are you sure to delete this data') . '?' }}
                </p>

                <form action="#" method="POST" id="__formDeleteSheetDoc">
                    @csrf

                    <input type="hidden" name="id" id="__idDeleteSheetDoc">
                    <div class="flex flex-row items-center justify-center gap-x-2">
                        <x-button type="reset" color="gray" id="__btnCancelDeleteSheetDoc">
                            {{ __('translation.No, Close') }}
                        </x-button>
                        <x-button type="submit" color="red" id="__btnSubmitDeleteSheetDoc">
                            {{ __('translation.Yes, Delete') }}
                        </x-button>
                    </div>
                </form>
            </x-modal.body>
        </x-modal.modal-small>

    @endif


    @push('top_css')
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.2.2/dist/sweetalert2.min.css">
    @endpush

    @push('bottom_js')
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.2.2/dist/sweetalert2.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/moment@2.29.1/moment.min.js"></script>
        <script src="{{ asset('pages/seller/sheet-docs/index/table.js?_=' . rand()) }}"></script>
    @endpush

</x-app-layout>
