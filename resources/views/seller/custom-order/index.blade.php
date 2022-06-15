<x-app-layout>

    @section('title')
        {{ __('translation.Custom Orders') }}
    @endsection

    @push('top_css')

    @endpush

    <div class="col-span-12">

        @include('partials.pages.order_management.tab_navigation')

        <x-card.card-default>
            <x-card.header>
                <x-card.title>
                    {{ __('translation.Custom Orders') }}
                </x-card.title>
            </x-card.header>
            <x-card.body>
                <div class="mb-6">
                    <div class="flex flex-col items-start">
                        <div class="w-full mb-6">
                            <x-button-link href="{{ route('custom-order.create') }}" color="green">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="w-3 h-3 bi bi-plus-circle" viewBox="0 0 16 16">
                                    <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
                                    <path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4z"/>
                                </svg>
                                <span class="ml-2">
                                    {{ __('translation.New Order') }}
                                </span>
                            </x-button>
                        </div>
                        <div class="w-full">
                            <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-5 gap-4">
                                <x-card.filter-button label="{{ __('translation.All') }}" data-status="-1" class="order-status-filter active">
                                    {{ number_format($orderStatusCountersTotal) }}
                                </x-card.filter-button>
                                @foreach ($orderStatusCounters as $orderStatus)
                                    <x-card.filter-button label="{{ $orderStatus['text'] }}" data-status="{{ $orderStatus['id'] }}" class="order-status-filter ">
                                        {{ number_format($orderStatus['total']) }}
                                    </x-card.filter-button>
                                @endforeach
                            </div>
                            {{-- <div class="w-full lg:w-11/12 xl:w-4/5 flex flex-row items-center justify-between">
                                <span class="mr-3" title="{{ __('translation.Filter by Order Status') }}">
                                    {{ __('translation.Status') }}:
                                </span>
                                <x-select name="status" id="__statusFilter">
                                    <option value="" disabled>
                                        {{ '- ' . __('translation.Select Status') . ' -' }}
                                    </option>
                                    <option value="-1" selected>
                                        {{ __('translation.All') }}
                                    </option>
                                    @foreach ($orderStatuses as $value => $text)
                                        <option value="{{ $value }}">
                                            {{ $text }}
                                        </option>
                                    @endforeach
                                </x-select>
                            </div> --}}
                        </div>
                    </div>
                </div>

                <x-alert-success class="mb-6 alert hide" id="__alertTableSuccess">
                    <div id="__alertTableSuccessContent"></div>
                </x-alert-success>

                <div class="w-full overflow-x-auto">
                    <table class="w-full" id="__customOrderTable">
                        <thead class="bg-blue-500">
                            <tr>
                                <th class="text-white">
                                    {{ __('translation.ID') }}
                                </th>
                                <th class="text-white">
                                    {{ __('translation.Customer Name') }}<br>
                                    {{ __('translation.Channel') }}
                                </th>
                                <th class="text-white">
                                    {{ __('translation.Amount') }}
                                </th>
                                <th class="text-white">
                                    {{ __('translation.Order Status') }}
                                </th>
                                <th class="text-white">
                                    {{ __('translation.Payment Status') }}
                                </th>
                                <th class="text-white">
                                    {{ __('translation.Created At') }}
                                </th>
                                <th class="text-white">
                                    {{ __('translation.Actions') }}
                                </th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </x-card.body>
        </x-card.card-default>
    </div>


    <x-modal.modal-small class="modal-hide" id="__modalDeleteOrder">
        <x-modal.header>
            <x-modal.title>
                {{ __('translation.Delete Order') }}
            </x-modal.title>
        </x-modal.header>
        <x-modal.body>

            <x-alert-danger class="mb-6 alert hide" id="__alertDeleteDanger">
                <div id="__alertDeleteDangerContent"></div>
            </x-alert-danger>

            <div class="mb-5">
                <p class="text-center">
                    {{ __('translation.Are you sure to delete this order') . '?' }}
                </p>
            </div>
            <div class="pb-5">
                <form method="post" action="{{ route('custom-order.destroy') }}" id="__formDeleteOrder" class="text-center">
                    <input type="hidden" name="id" id="__idDeleteOrder">
                    <x-button type="reset" color="gray" id="__btnCancelDeleteOrder">
                        {{ __('translation.No, Close') }}
                    </x-button>
                    <x-button type="submit" color="red" id="__btnSubmitDeleteOrder">
                        {{ __('translation.Yes, Delete') }}
                    </x-button>
                </form>
            </div>
        </x-modal.body>
    </x-modal.modal-small>


    @push('bottom_js')
        <script>
            const customOrderUrl = '{{ route('custom-order.datatable') }}';

            const textYesDelete = '{{ __('translation.Yes, Delete') }}';
            const textDeleting = '{{ __('translation.Deleting') }}';

            var statusFilter = $('#__statusFilter').val();

            const loadCustomOrderTable = (status = -1) => {
                $('#__customOrderTable').DataTable({
                    bDestroy: true,
                    serverSide: true,
                    processing: true,
                    ajax: {
                        type: 'GET',
                        url: customOrderUrl,
                        data: {
                            orderStatus: status
                        }
                    },
                    columnDefs: [
                        {
                            targets: [6],
                            orderable: false
                        }
                    ],
                    orders: [
                        [ 0, 'desc' ]
                    ],
                    paginationType: 'numbers'
                });
            }


            loadCustomOrderTable();


            $('.order-status-filter').on('click', function() {
                statusFilter = $(this).data('status');

                $('.order-status-filter').each(function() {
                    $(this).removeClass('active');
                });

                $(this).addClass('active');

                loadCustomOrderTable(statusFilter);
            });


            const deleteOrder = (el) => {
                let orderId = el.getAttribute('data-id');
                $('#__idDeleteOrder').val(orderId);

                $('#__modalDeleteOrder').removeClass('modal-hide');
                $('body').addClass('modal-open');
            }


            $('#__btnCancelDeleteOrder').on('click', function() {
                $('#__modalDeleteOrder').addClass('modal-hide');
                $('body').removeClass('modal-open');

                $('.alert').addClass('hide');
                $('#__alertSuccessTableContent').html(null);
                $('#__alertDeleteDangerContent').html(null);
            });


            $('#__formDeleteOrder').on('submit', function(event) {
                event.preventDefault();

                let formData = new FormData($(this)[0]);
                const deleteOrderUrl = $(this).attr('action');

                $.ajax({
                    type: 'POST',
                    url: deleteOrderUrl,
                    data: formData,
                    processData: false,
                    contentType: false,
                    beforeSend: function() {
                        $('.alert').addClass('hide');
                        $('#__alertTableSuccessContent').html(null);
                        $('#__alertDeleteDangerContent').html(null);

                        $('#__btnCancelCreateOrder').attr('disabled', true);
                        $('#__btnSubmitCreateOrder').attr('disabled', true).html(textDeleting);
                    },
                    success: function(responseData) {
                        $('#__alertTableSuccessContent').html(responseData.message);
                        $('#__alertTableSuccess').removeClass('hide');

                        $('#__modalDeleteOrder').addClass('modal-hide');
                        $('body').removeClass('modal-open');

                        setTimeout(() => {
                            $('html, body').animate({
                                scrollTop: 0
                            }, 500);
                        }, 500);

                        loadCustomOrderTable(statusFilter);
                    },
                    error: function(error) {
                        let responseJson = error.responseJSON;

                        $('#__btnCancelCreateOrder').attr('disabled', false);
                        $('#__btnSubmitCreateOrder').attr('disabled', false).html(textYesDelete);

                        if (error.status == 422) {
                            let errorFields = Object.keys(responseJson.errors);
                            errorFields.map(field => {
                                $('#__alertDeleteDangerContent').append(
                                    $('<span/>', {
                                        class: 'block mb-1',
                                        html: `- ${responseJson.errors[field][0]}`
                                    })
                                );
                            });

                        } else {
                            $('#__alertDeleteDangerContent').html(responseJson.message);

                        }

                        $('#__alertDeleteDanger').removeClass('hide');
                    }
                });

                return false;
            });
        </script>
    @endpush

</x-app-layout>
