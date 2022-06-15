<x-app-layout>

    @section('title')
        {{ __('translation.Sheets Data ') . ' - ' . $sheetDoc->file_name }}
    @endsection


    @if(\App\Models\Role::checkRolePermissions('Can access menu: TPK Packing Data'))
        <div class="col-span-12">
            <x-card.card-default>
                <x-card.header>
                    <x-card.title>
                        {{ __('translation.Sheets Data ') . ' - ' . $sheetDoc->file_name }}
                    </x-card.title>
                </x-card.header>
                <x-card.body>
                    <div class="mb-8 border border-solid border-gray-300 rounded-lg p-6 bg-gray-50">
                        <div class="px-4">
                            <span>
                                {{ __('translation.Document Information') }}
                            </span>
                        </div>
                        <div class="mt-4 overflow-x-auto">
                            <table class="w-full">
                                <tbody>
                                    <tr class="block mb-2 sm:table-row sm:mb-0">
                                        <td class="block px-4 py-0 align-top sm:table-cell sm:w-1/2 sm:py-1 md:w-1/3">
                                            {{ __('translation.File Name') }}
                                        </td>
                                        <td class="hidden w-1 py-1 align-top sm:table-cell">:</td>
                                        <td class="block px-4 py-0 align-top sm:table-cell sm:py-1">
                                            <span class="font-bold">
                                                {{ $sheetDoc->file_name }}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr class="block mb-2 sm:table-row sm:mb-0">
                                        <td class="block px-4 py-0 align-top sm:table-cell sm:w-1/2 sm:py-1 md:w-1/3">
                                            {{ __('translation.Spreadsheet ID') }}
                                        </td>
                                        <td class="hidden w-1 py-1 align-top sm:table-cell">:</td>
                                        <td class="block px-4 py-0 align-top sm:table-cell sm:py-1">
                                            <span class="font-bold whitespace-normal">
                                                {{ $sheetDoc->spreadsheet_id }}
                                            </span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="flex flex-col items-center justify-center sm:flex-row sm:justify-start">
                        <x-button type="button" color="green" id="__btnAddSheetName">
                            <i class="bi bi-plus-circle"></i>
                            <span class="ml-2">
                                {{ __('translation.Add Sheet') }}
                            </span>
                        </x-button>
                    </div>
                    <div class="mt-8 w-full overflow-x-auto">
                        <table class="w-full" id="__sheetNameTable">
                            <thead>
                                <tr>
                                    <th class="px-2 py-4 bg-blue-500 text-white">
                                        #
                                    </th>
                                    <th class="px-2 py-4 bg-blue-500 text-white">
                                        {{ __('translation.Sheet Name') }}
                                    </th>
                                    <th class="px-2 py-4 bg-blue-500 text-white">
                                        {{ __('translation.Auto Sync') }}
                                    </th>
                                    <th class="px-2 py-4 bg-blue-500 text-white">
                                        {{ __('translation.Last Sync At') }}
                                    </th>
                                    <th class="px-2 py-4 bg-blue-500 text-white">
                                        {{ __('translation.Sync Status') }}
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

        <x-modal.modal-small id="__modalAddSheetName">
            <x-modal.header>
                <x-modal.title>
                    {{ __('translation.Add Sheet') }}
                </x-modal.title>
            </x-modal.header>
            <x-modal.body>

                <form action="#" method="POST" id="__formAddSheetName">
                    @csrf

                    <x-form.form-group>
                        <x-form.label for="__sheetNameAddSheetName">
                            {{ __('translation.Sheet Name') }}
                            <x-form.required-mark />
                        </x-form.label>
                        <x-form.input type="text" name="sheet_name" id="__sheetNameAddSheetName" />
                    </x-form.form-group>
                    <x-form.form-group>
                        <x-form.label for="__allow_to_syncAddSheetName">
                            {{ __('translation.Auto Sync') }}
                            <x-form.required-mark />
                        </x-form.label>
                        <div class="mt-1 flex flex-row items-center gap-x-4">
                            <x-form.input-radio name="allow_to_sync" id="__allow_to_syncAddSheetName_0" value="0" checked="true">
                                {{ __('translation.No') }}
                            </x-form.input-radio>
                            <x-form.input-radio name="allow_to_sync" id="__allow_to_syncAddSheetName_1" value="1">
                                {{ __('translation.Yes') }}
                            </x-form.input-radio>
                        </div>
                    </x-form.form-group>
                    <div class="flex flex-row items-center justify-center gap-x-2">
                        <x-button type="reset" color="gray" id="__btnCancelAddSheetName">
                            {{ __('translation.Cancel') }}
                        </x-button>
                        <x-button type="submit" color="blue" id="__btnSubmitAddSheetName">
                            {{ __('translation.Save') }}
                        </x-button>
                    </div>
                </form>
            </x-modal.body>
        </x-modal.modal-small>


        <x-modal.modal-small id="__modalEditSheetName">
            <x-modal.header>
                <x-modal.title>
                    {{ __('translation.Edit Sheet') }}
                </x-modal.title>
            </x-modal.header>
            <x-modal.body>

                <form action="#" method="POST" id="__formEditSheetName">
                    @csrf

                    <input type="hidden" name="id" id="__idEditSheetName">
                    <x-form.form-group>
                        <x-form.label for="__sheetNameEditSheetName">
                            {{ __('translation.Sheet Name') }}
                            <x-form.required-mark />
                        </x-form.label>
                        <x-form.input type="text" name="sheet_name" id="__sheetNameEditSheetName" />
                    </x-form.form-group>
                    <x-form.form-group>
                        <x-form.label for="__allow_to_syncEditSheetName">
                            {{ __('translation.Auto Sync') }}
                            <x-form.required-mark />
                        </x-form.label>
                        <div class="mt-1 flex flex-row items-center gap-x-4">
                            <x-form.input-radio name="allow_to_sync" id="__allow_to_syncEditSheetName_0" value="0">
                                {{ __('translation.No') }}
                            </x-form.input-radio>
                            <x-form.input-radio name="allow_to_sync" id="__allow_to_syncEditSheetName_1" value="1">
                                {{ __('translation.Yes') }}
                            </x-form.input-radio>
                        </div>
                    </x-form.form-group>
                    <div class="flex flex-row items-center justify-center gap-x-2">
                        <x-button type="reset" color="gray" id="__btnCancelEditSheetName">
                            {{ __('translation.Cancel') }}
                        </x-button>
                        <x-button type="submit" color="blue" id="__btnSubmitEditSheetName">
                            {{ __('translation.Save') }}
                        </x-button>
                    </div>
                </form>
            </x-modal.body>
        </x-modal.modal-small>

        <x-modal.modal-small id="__modalDeleteSheetName">
            <x-modal.header>
                <x-modal.title>
                    {{ __('translation.Delete Sheet') }}
                </x-modal.title>
            </x-modal.header>
            <x-modal.body>

                <p class="mb-4 text-center">
                    {{ __('translation.Are you sure to delete this data') . '?' }}
                </p>

                <form action="#" method="POST" id="__formDeleteSheetName">
                    @csrf

                    <input type="hidden" name="id" id="__idDeleteSheetName">
                    <div class="flex flex-row items-center justify-center gap-x-2">
                        <x-button type="reset" color="gray" id="__btnCancelDeleteSheetName">
                            {{ __('translation.No, Close') }}
                        </x-button>
                        <x-button type="submit" color="red" id="__btnSubmitDeleteSheetName">
                            {{ __('translation.Yes, Delete') }}
                        </x-button>
                    </div>
                </form>
            </x-modal.body>
        </x-modal.modal-small>

        <x-modal.modal-small id="__modalSyncNowSheetName">
            <x-modal.header>
                <x-modal.title>
                    {{ __('translation.Sync the Sheet') }}
                </x-modal.title>
            </x-modal.header>
            <x-modal.body>

                <p class="mb-4 text-center">
                    {{ __('translation.Are you sure to sync this sheet') . '?' }}<br>
                    <span class="font-bold" id="__syncNameSyncNowSheetName"></span>
                </p>

                <form action="#" method="POST" id="__formSyncNowSheetName">
                    @csrf

                    <input type="hidden" name="id" id="__idSyncNowSheetName">
                    <div class="flex flex-row items-center justify-center gap-x-2">
                        <x-button type="reset" color="gray" id="__btnCancelSyncNowSheetName">
                            {{ __('translation.No, Close') }}
                        </x-button>
                        <x-button type="submit" color="blue" id="__btnSubmitSyncNowSheetName">
                            {{ __('translation.Yes, Sync Now') }}
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
        <script>
            const sheetDocId = {{ $sheetDoc->id }};
        </script>
        <script src="{{ asset('pages/seller/sheet-names/index/table.js?_=' . rand()) }}"></script>
        <script src="{{ asset('pages/seller/sheet-names/index/sync-now.js?_=' . rand()) }}"></script>
    @endpush

</x-app-layout>
