<x-app-layout>
    @section('title')
        {{ __('translation.Stock History') }}
    @endsection

    @push('top_css')
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/jquery-datetimepicker@2.5.21/build/jquery.datetimepicker.min.css">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/jquery-datatables-checkboxes@1.2.12/css/dataTables.checkboxes.css">
    @endpush

    @if (in_array('Can access menu: Stock Adjust - History', session('assignedPermissions')))
    <x-card title="{{ __('translation.Stock History') }}">

        <x-alert-danger class="alert hidden" id="__alertDangerTable">
            <span id="__contentAlertDangerTable"></span>
        </x-alert-danger>

        <x-alert-success class="alert hidden" id="__alertSuccessTable">
            <span id="__contentAlertSuccessTable"></span>
        </x-alert-success>

        <div class="flex flex-row justify-end mb-5">
            <x-button type="button" color="red" id="__btnBulkDelete">
                {{ __('translation.Bulk Delete') }}
            </x-button>
        </div>

        <div class="w-full overflow-x-auto">
            <table class="table" id="__tblProductHistory">
                <thead>
                    <tr>
                        <th></th>
                        <th>
                            {{ __('translation.ID') }}
                        </th>
                        <th>
                            {{ __('translation.Product Name') }}
                        </th>
                        <th>
                            {{ __('translation.Product Code') }}
                        </th>
                        <th>
                            {{ __('translation.Type') }}
                        </th>
                        <th>
                            {{ __('translation.Quantity') }}
                        </th>
                        <th>
                            {{ __('translation.User') }}
                        </th>
                        <th>
                            {{ __('translation.Date Time') }}
                        </th>
                        <th>
                            {{ __('translation.Actions') }}
                        </th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </x-card>
    @endif

    <x-modal.modal-large class="modal-hide" id="__modal_EditStock">
        <x-modal.header>
            <x-modal.title>
                {{ __('translation.Update Stock History') }}
            </x-modal.title>
        </x-modal.header>
        <x-modal.body>

            <x-alert-danger class="alert hidden" id="__alertDangerEditStock">
                <span id="__content_AlertDangerEditStock"></span>
            </x-alert-danger>

            <x-alert-success class="alert hidden" id="__alertSuccessEditStock">
                <span id="__content_AlertSuccessEditStock"></span>
            </x-alert-success>

            <form method="post" action="#" id="__form_EditStock">
                @csrf
                <input type="hidden" name="id" id="__id_EditStock">
                <input type="hidden" name="product_id" id="__product_id_EditStock">

                <div class="grid grid-col-1 mb-5">
                    <div>
                        <x-label>
                            {{ __('translation.Product') }} <x-form.required-mark />
                        </x-label>
                        <x-input type="text" id="__product_name_EditStock" class="bg-gray-100" disabled />
                    </div>
                </div>

                <div class="grid grid-col-1 md:grid-cols-2 gap-5">
                    <div>
                        <x-label>
                            {{ __('translation.Date Time') }} <x-form.required-mark />
                        </x-label>
                        <x-input type="text" name="datetime" id="__date_EditStock" />
                    </div>
                    <div>
                        <x-label>
                            {{ __('translation.Type') }} <x-form.required-mark />
                        </x-label>
                        <div class="mt-3 flex flex-row gap-5">
                            <div>
                                <x-form.input-radio name="check_in_out" id="__check_in_out_EditStock" checked="true" />
                            </div>
                        </div>
                    </div>
                    <div>
                        <x-label>
                            {{ __('translation.Quantity') }} <x-form.required-mark />
                        </x-label>
                        <x-input type="number" name="quantity" id="__quantity_EditStock" min="1" />
                    </div>
                </div>

                <div class="flex justify-end py-6">
                    <x-button type="reset" color="gray" class="mr-1" id="__btnCancel_EditStock">
                        {{ __('translation.Cancel') }}
                    </x-button>
                    <x-button type="submit" color="blue" id="__btnSubmit_EditStock">
                        {{ __('translation.Update') }}
                    </x-button>
                </div>
            </form>
        </x-modal.body>
    </x-modal.modal-large>


    <x-modal.alert class="modal-hide" id="__modal_DeleteStock">
        <x-modal.header>
            <x-modal.title>
                {{ __('translation.Delete Stock') }}
            </x-modal.title>
        </x-modal.header>
        <x-modal.body>

            <x-alert-danger class="alert hidden" id="__alertDangerDeleteStock">
                <span id="__content_AlertDangerDeleteStock"></span>
            </x-alert-danger>

            <x-alert-success class="alert hidden" id="__alertSuccessDeleteStock">
                <span id="__content_AlertSuccessDeleteStock"></span>
            </x-alert-success>

            <form method="post" action="#" id="__form_DeleteStock">
                @csrf
                <input type="hidden" name="id" id="__id_DeleteStock">
                <p class="text-center mt-2">
                    {{ __('translation.Are you sure to delete this data?') }}
                </p>

                <div class="flex justify-center py-6">
                    <x-button type="reset" color="gray" class="mr-1" id="__btnCancel_DeleteStock">
                        {{ __('translation.No') }}
                    </x-button>
                    <x-button type="submit" color="red" id="__btnSubmit_DeleteStock">
                        {{ __('translation.Yes, Delete') }}
                    </x-button>
                </div>
            </form>
        </x-modal.body>
    </x-modal.alert>


    <x-modal.alert class="hidden" id="__modalConfirmBulkDelete">
        <x-modal.header>
            <x-modal.title>
                {{ __('translation.Confirm') }}
            </x-modal.title>
        </x-modal.header>
        <x-modal.body>
            <x-alert-danger class="alert hidden" id="__alertDangerDeleteModal">
                <div id="__contentAlertDangerDeleteModal"></div>
            </x-alert-danger>

            <div class="mb-4">
                <p class="text-center">
                    {{ __('translation.Are you sure to delete the selected data?') }}
                </p>
            </div>
            <div class="text-center pb-6">
                <x-button type="button" color="gray" id="__btnNoConfirmBulkDelete">
                    {{ __('translation.No, Close') }}
                </x-button>
                <x-button type="button" color="red" id="__btnYesConfirmBulkDelete">
                    {{ __('translation.Yes Delete') }}
                </x-button>
            </div>
        </x-modal.body>
    </x-modal.alert>


    <x-modal.alert class="hidden" id="__modalNoBulkDelete">
        <x-modal.header>
            <x-modal.title>
                {{ __('translation.Warning') }}
            </x-modal.title>
        </x-modal.header>
        <x-modal.body>
            <div class="mb-4">
                <p class="text-center">
                    {{ __('translation.Please select at least one row.') }}
                </p>
            </div>
            <div class="text-center pb-6">
                <x-button type="button" color="green" id="__closeModalNoBulkDelete">
                    {{ __('translation.Ok, Understand') }}
                </x-button>
            </div>
        </x-modal.body>
    </x-modal.alert>


    @push('bottom_js')
    <script src="https://cdn.jsdelivr.net/npm/jquery-datetimepicker@2.5.21/build/jquery.datetimepicker.full.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery-datatables-checkboxes@1.2.12/js/dataTables.checkboxes.min.js"></script>

    <script>
        const inOutDataTableUrl = '{{ route('in-out-datatable') }}';
        const inOutHistoryDetailUrl = '{{ route('in-out-history') }}';
        const inOutHistoryUpdateUrl = '{{ route('in-out-history-update') }}';
        const inOutHistoryDeleteUrl = '{{ route('in-out-history-delete') }}';
        const productSelectTwoUrl = '{{ route('product.select2') }}';
        const inOutHistoryBulkDeleteUrl = '{{ route('delete quantity log bulk') }}';

        const TYPE_ADD_VALUE = {{ App\Models\StockLog::CHECK_IN_OUT_ADD }};
        const TYPE_REMOVE_VALUE = {{ App\Models\StockLog::CHECK_IN_OUT_REMOVE }};

        const TYPE_ADD_LABEL = '{{ __('translation.Add') }}';
        const TYPE_REMOVE_LABEL = '{{ __('translation.Remove') }}';


        var tableProductHistory = $('#__tblProductHistory').DataTable({
            // bDestroy: true,
            serverSide: true,
            processing: true,
            pagingType: 'numbers',
            ajax: {
                type: 'GET',
                url: inOutDataTableUrl
            },
            order: [
                [ 7, 'desc' ]
            ],
            columnDefs: [
                {
                    targets: [0],
                    orderable: false,
                    checkboxes: {
                        selectRow: true
                    }
                },
                {
                    targets: [8],
                    orderable: false,
                    className: 'text-center'
                }
            ],
            select: {
                style: 'multiple'
            }
        });


        $('#__date_EditStock').datetimepicker({
            format: 'Y-m-d H:i'
        });


        $('#__btnCancel_EditStock').click(function() {
            $('body').removeClass('modal-open');
            $('#__modal_EditStock').addClass('modal-hide');
            $('.alert').addClass('hidden');
        });


        const editHistory = el => {
            let historyId = el.getAttribute('data-id');

            $.ajax({
                type: 'GET',
                url: `${inOutHistoryDetailUrl}/${historyId}`
            })
            .done(response => {
                let stock = response.data;
                let dateTime = new Date(stock.date);
                let dateTimeDay = (dateTime.getDate() < 10) ? `0${dateTime.getDate()}` : dateTime.getDate();
                let dateTimeMonth = (dateTime.getMonth() < 10) ? `0${dateTime.getMonth() + 1}` : `${dateTime.getMonth()+1}`;
                let dateTimeYear = dateTime.getFullYear();
                let dateTimeHour = (dateTime.getHours() < 10) ? `0${dateTime.getHours()}` : dateTime.getHours();
                let dateTimeMinute = (dateTime.getMinutes() < 10) ? `0${dateTime.getMinutes()}` : dateTime.getMinutes();
                let dateTimeFieldValue = `${dateTimeYear}-${dateTimeMonth}-${dateTimeDay} ${dateTimeHour}:${dateTimeMinute}`;

                $('.alert').addClass('hidden');

                $('body').addClass('modal-open');
                $('#__modal_EditStock').removeClass('modal-hide');

                $('#__id_EditStock').val(stock.id);

                $('#__product_id_EditStock').val(stock.product.id);
                $('#__product_name_EditStock').val(stock.product.name);

                $('#__date_EditStock').val(dateTimeFieldValue);

                $('#__check_in_out_EditStock').val(stock.check_in_out);
                $('#__check_in_out_EditStock').prop('checked', true);

                $('#__check_in_out_EditStock').parent().find('span').html(TYPE_ADD_LABEL);
                if (stock.check_in_out == TYPE_REMOVE_VALUE) {
                    $('#__check_in_out_EditStock').parent().find('span').html(TYPE_REMOVE_LABEL);
                }

                $('#__quantity_EditStock').val(stock.quantity);
            })
            .fail(response => {
                let responsJson = response.responseJSON;

                $('.alert').addClass('hidden');
                $('#__alertDangerTable').removeClass('hidden');
                $('#__contentAlertDangerTable').html(responsJson.message);
            });
        }


        $('#__form_EditStock').on('submit', function(event) {
            event.preventDefault();

            let formData = new FormData($(this)[0]);

            $.ajax({
                type: 'POST',
                url: inOutHistoryUpdateUrl,
                data: formData,
                processData: false,
                contentType: false,
                enctype: 'multipart/form-data',
                beforeSend: function() {
                    $('.alert').addClass('hidden');
                    $('#__btnCancel_EditStock').attr('disabled', true);
                    $('#__btnSubmit_EditStock').attr('disabled', true).html('Updating...');
                }
            })
            .done(responseJson => {
                $('#__btnCancel_EditStock').attr('disabled', false);
                $('#__btnSubmit_EditStock').attr('disabled', false).html('Update');

                $('#__modal_EditStock').addClass('modal-hide');
                $('body').removeClass('modal-open');

                $('#__alertSuccessTable').removeClass('hidden');
                $('#__contentAlertSuccessTable').html(null);
                $('#__contentAlertSuccessTable').html(responseJson.message);

                tableProductHistory.ajax.reload(null, false);
            })
            .fail(response => {
                let responseJson = response.responseJSON;

                $('#__btnCancel_EditStock').attr('disabled', false);
                $('#__btnSubmit_EditStock').attr('disabled', false).html('Update');

                $('.alert').addClass('hidden');
                $('#__alertDangerEditStock').removeClass('hidden');
                $('#__content_AlertDangerEditStock').html(null);

                if (response.status === 422) {
                    let errorFields = Object.keys(responseJson.errors);
                    errorFields.map(field => {
                        $('#__content_AlertDangerEditStock').append(
                            $('<p/>', {
                                html: responseJson.errors[field][0]
                            })
                        );
                    });
                }
                else {
                    $('#__content_AlertDangerEditStock').html(responseJson.message);
                }
            });

            return false;
        });


        const deleteStock = el => {
            let stockId = el.getAttribute('data-id');

            $('#__id_DeleteStock').val(stockId);

            $('body').addClass('modal-open');
            $('#__modal_DeleteStock').removeClass('modal-hide');
        };


        $('#__btnCancel_DeleteStock').click(function() {
            $('.alert').addClass('hidden');
            $('body').removeClass('modal-open');
            $('#__modal_DeleteStock').addClass('modal-hide');
        });


        $('#__form_DeleteStock').on('submit', function(event) {
            event.preventDefault();

            let formData = new FormData($(this)[0]);

            $.ajax({
                type: 'POST',
                url: inOutHistoryDeleteUrl,
                data: formData,
                processData: false,
                contentType: false,
                beforeSend: function() {
                    $('.alert').addClass('hidden');
                    $('#__btnCancel_DeleteStock').attr('disabled', true);
                    $('#__btnSubmit_DeleteStock').attr('disabled', true).html('Deleting...');
                }
            })
            .done(responseJson => {
                $('#__btnCancel_DeleteStock').attr('disabled', false);
                $('#__btnSubmit_DeleteStock').attr('disabled', false).html('Yes, Delete');

                $('#__modal_DeleteStock').addClass('modal-hide');
                $('body').removeClass('modal-open');

                $('#__alertSuccessTable').removeClass('hidden');
                $('#__contentAlertSuccessTable').html(null);
                $('#__contentAlertSuccessTable').html(responseJson.message);

                tableProductHistory.ajax.reload(null, false);
            })
            .fail(response => {
                let responseJson = response.responseJSON;

                $('#__btnCancel_DeleteStock').attr('disabled', false);
                $('#__btnSubmit_DeleteStock').attr('disabled', false).html('Yes, Delete');

                $('.alert').addClass('hidden');
                $('#__alertDangerDeleteStock').removeClass('hidden');
                $('#__content_AlertDangerDeleteStock').html(null);

                if (response.status === 422) {
                    let errorFields = Object.keys(responseJson.errors);
                    errorFields.map(field => {
                        $('#__content_AlertDangerDeleteStock').append(
                            $('<p/>', {
                                html: responseJson.errors[field][0]
                            })
                        );
                    });
                }
                else {
                    $('#__content_AlertDangerDeleteStock').html(responseJson.message);
                }
            });

            return false;
        });


        $('#__btnBulkDelete').click(function() {
            selectedRows = tableProductHistory.column(0).checkboxes.selected();

            if (selectedRows.length == 0) {
                $('#__modalNoBulkDelete').removeClass('hidden');
                $('body').addClass('modal-open');
            } else {
                $('#__modalConfirmBulkDelete').removeClass('hidden');
                $('body').addClass('modal-open');
            }
        });


        $('#__closeModalNoBulkDelete').click(function() {
            $('#__modalNoBulkDelete').addClass('hidden');
            $('body').removeClass('modal-open');
        });


        $('#__btnNoConfirmBulkDelete').click(function() {
            $('#__modalConfirmBulkDelete').addClass('hidden');
            $('body').removeClass('modal-open');
            $('.alert').addClass('hidden');
        });


        $('#__btnYesConfirmBulkDelete').click(function() {
            var bulkDeleteIds = [];

            let formData = new FormData();
            formData.append('_token', $('meta[name="csrf-token"]').attr('content'));

            $.each(selectedRows, function(index, rowId) {
                formData.append('id[]', rowId);
            });

            $.ajax({
                type: 'POST',
                url: inOutHistoryBulkDeleteUrl,
                data: formData,
                processData: false,
                contentType: false,
                beforeSend: function() {
                    $('#__btnNoConfirmBulkDelete').attr('disabled', true);
                    $('#__btnYesConfirmBulkDelete').attr('disabled', true).html('{{ __('translation.Deleting') }}');
                    $('.alert').addClass('hidden');
                },
                success: function(responseJson) {
                    $('#__btnNoConfirmBulkDelete').attr('disabled', false);
                    $('#__btnYesConfirmBulkDelete').attr('disabled', false).html('{{ __('translation.Yes, Delete') }}');

                    $('html, body').animate({
                        scrollTop: 0
                    }, 500);

                    tableProductHistory.ajax.reload(null);

                    $('#__contentAlertSuccessTable').html(null);
                    $('#__contentAlertSuccessTable').html(responseJson.message);
                    $('#__alertSuccessTable').removeClass('hidden');

                    $('#__modalConfirmBulkDelete').addClass('hidden');
                    $('body').removeClass('modal-open');
                },
                error: function(error) {
                    let responseJson = error.responseJSON;

                    $('#__btnNoConfirmBulkDelete').attr('disabled', false);
                    $('#__btnYesConfirmBulkDelete').attr('disabled', false).html('{{ __('translation.Yes, Delete') }}');

                    $('#__contentAlertDangerDeleteModal').html(null);

                    if (error.status == 422) {
                        let errorFields = Object.keys(responseJson.errors);
                        errorFields.map(field => {
                            $('#__content_alertDanger').append(
                                $('<p/>', {
                                    html: responseJson.errors[field][0]
                                })
                            );
                        });

                    } else {
                        $('#__contentAlertDangerDeleteModal').html(responseJson.message);
                    }

                    $('#__alertDangerDeleteModal').removeClass('hidden');
                }
            });
        });
    </script>
    @endpush

</x-app-layout>
