<x-app-layout>

    @section('title')
        {{ ucwords(__('translation.shopee_products')) }}
    @endsection

    @push('top_css')
        <style>
            .missing_info_messages .alert {
                padding: 5px 10px;
                margin-bottom: 5px;
            }
        </style>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/jquery-datatables-checkboxes@1.2.12/css/dataTables.checkboxes.css">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.19/dist/sweetalert2.min.css">
    @endpush

    @push('bottom_js')
        <link rel="stylesheet" href="{{ asset('pages/seller/wc_products/index/index.css?_=' . rand()) }}">
    @endpush

    @if(\App\Models\Role::checkRolePermissions('Can access menu: Shopee - Settings'))
        <div class="col-span-12">

            <x-card.card-default>
                <x-card.header>
                    <x-card.title>
                        {{ ucwords(__('translation.Shopee discount list')) }}
                    </x-card.title>
                </x-card.header>
                <x-card.body>

                    @if(session()->has('error'))
                        <div class="alert alert-danger mb-3 background-danger" role="alert">
                            {{ session()->get('error') }}
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif

                    @if(session()->has('success'))
                        <div class="alert alert-success mb-3 background-success" role="alert">
                            {{ session()->get('success') }}
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif

                    <div id="messageStatus"></div>

                    <div class="w-full sm:w-4/5 lg:w-3/4 mb-4">
                        <div class="flex flex-col sm:flex-row gap-2">
                            @if (isset($shops))
                                <x-select name="website_id" id="website_id" class="select-shop" style="max-width:200px;">
                                    @foreach ($shops as $shop)
                                        <option value="{{ $shop->shop_id }}">
                                            {{ $shop->shop_name }}
                                        </option>
                                    @endforeach
                                </x-select>
                            @endif

                            <x-select name="shopee_product_discount_status" id="shopee_product_discount_status" class="ml-2" style="max-width:200px;">
                                <option value="all">
                                    {{ ucwords(__('translation.all')) }}
                                </option>
                                <option value="upcoming">
                                    {{ ucwords(__('translation.upcoming')) }}
                                </option>
                                <option value="ongoing">
                                    {{ ucwords(__('translation.ongoing')) }}
                                </option>
                                <option value="expired">
                                    {{ ucwords(__('translation.expired')) }}
                                </option>
                            </x-select>

                            <x-button type="button" color="green" class="btn__sync-discount">
                                <i class="bi bi-arrow-repeat text-base"></i>
                                <span class="ml-2">
                                    {{ ucwords(__('translation.Sync discount')) }}
                                </span>
                            </x-button>
                        </div>
                    </div>

                    <x-alert-success id="__alertSuccessShopeeTable" class="alert hidden"></x-alert-success>
                    <x-alert-danger id="__alertDangerShopeeTable" class="alert hidden"></x-alert-danger>

                    <div class="w-full mt-4 overflow-x-auto">
                        <table class="w-full" id="shopeeProductDiscountTable">
                            <thead>
                            <tr>
                                <th class="px-4 py-2 bg-blue-500 text-white">

                                </th>
                                <th class="px-4 py-2 bg-blue-500 text-white">
                                    {{ ucwords(__('translation.name')) }}
                                </th>
                                <th class="px-4 py-2 bg-blue-500 text-white">
                                    {{ ucwords(__('translation.status')) }}
                                </th>
                                <th class="px-4 py-2 bg-blue-500 text-white">
                                    {{ ucwords(__('translation.start')) }}
                                </th>
                                <th class="px-4 py-2 bg-blue-500 text-white">
                                    {{ ucwords(__('translation.end')) }}
                                </th>
                                <th class="px-4 py-2 bg-blue-500 text-white">
                                    {{ ucwords(__('translation.renew')) }}
                                </th>
                            </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </x-card.body>
            </x-card.card-default>
        </div>

        <x-modal.modal-small id="__modalSyncDiscount">
            <x-modal.header>
                <x-modal.title>
                    {{ ucwords(__('translation.Sync discount list')) }}
                </x-modal.title>
            </x-modal.header>
            <x-modal.body>

                <x-alert-info id="__alertInfoSyncDiscount" class="alert hidden"></x-alert-info>
                <x-alert-danger id="__alertDangerSyncDiscount" class="alert hidden"></x-alert-danger>

                <form action="{{ route("shopee.product.discount.sync") }}" method="POST" id="__formSyncDiscount">
                    @csrf
                    <div class="grid grid-cols-1 gap-4 sm:gap-x-8">
                        <div>
                            <x-label id="__shop_idSyncProduct">
                                {{ ucwords(__('translation.shop')) }}
                            </x-label>
                            <x-select name="shop_id" id="__shop_idSyncShopDiscount">
                                <option value="">
                                    {{ ucwords(__('translation.select_shop')) }}
                                </option>
                                @if (isset($shops))
                                    @foreach ($shops as $shop)
                                        <option value="{{ $shop->shop_id }}">
                                            {{ $shop->shop_name }}
                                        </option>
                                    @endforeach
                                @endif
                            </x-select>
                        </div>
                    </div>

                    <div class="mt-4 pb-3">
                        <div class="flex flex-row items-center justify-center gap-2">
                            <x-button type="reset" color="gray" id="__btnCancelSyncDiscount">
                                {{ __('translation.cancel') }}
                            </x-button>
                            <x-button type="submit" color="blue" id="__btnSubmitSyncDiscount">
                                {{ __('translation.load_data') }}
                            </x-button>
                        </div>
                    </div>
                </form>
            </x-modal.body>
        </x-modal.modal-small>
    @endif

    @push('bottom_js')
        <script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/jquery-datatables-checkboxes@1.2.12/js/dataTables.checkboxes.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.19/dist/sweetalert2.min.js"></script>

        <script type="text/javascript">
            $('body').on('click', '.btn__sync-discount', function () {
                $('#__modalSyncDiscount').doModal('open');
            });

            $('#__btnCancelSyncDiscount').on('click', function () {
                $('#__modalSyncDiscount').doModal('close');
                $('.alert').addClass('hidden').find('.alert-content').html(null);
            });

            let selectedWebsiteId = '';
            let discountStatus = '';

            $(document).ready(function() {
                selectedWebsiteId = $("#website_id").find("option:selected").val();
                discountStatus = $("#shopee_product_discount_status").find("option:selected").val();

                if (typeof(selectedWebsiteId) !== "undefined") {
                    loadShopeeProductBoostTable(selectedWebsiteId, discountStatus);
                }
            });


            $(document).on('change', '#website_id', function() {
                selectedWebsiteId = $(this).val();
                loadShopeeProductBoostTable(selectedWebsiteId);
            });


            $(document).on('change', '#shopee_product_discount_status', function() {
                discountStatus = $(this).val();
                loadShopeeProductBoostTable(selectedWebsiteId, discountStatus);
            });


            const loadShopeeProductBoostTable = (websiteId = null) => {
                $('#shopeeProductDiscountTable').DataTable({
                    bDestroy: true,
                    processing: true,
                    serverSide: true,
                    iDisplayLength: 10,
                    ajax: {
                        type: 'GET',
                        url: '{{ route("shopee.product.discount.data") }}',
                        data: {
                            website_id: websiteId,
                            status: discountStatus
                        }
                    },
                    columns: [
                        {
                            name: 'checkbox',
                            data: 'checkbox',
                            checkboxes: {
                                'selectRow': true
                            },
                            orderable: false
                        },
                        {
                            name: 'name',
                            data: 'name'
                        },
                        {
                            name: 'status',
                            data: 'status'
                        },
                        {
                            name: 'start',
                            data: 'start'
                        },
                        {
                            name: 'end',
                            data: 'end'
                        },
                        {
                            name: 'renew',
                            data: 'renew'
                        }
                    ],
                    select : {
                        style: 'multi'
                    }
                });
            }

            const textSyncing = '{{ __('translation.syncing') }}';
            const textLoadData = '{{ __('translation.load_data') }}';

            $('#__formSyncDiscount').on('submit', function (event) {
                event.preventDefault();

                const actionUrl = $(this).attr('action');
                const formData = new FormData($(this)[0]);

                $.ajax({
                    type: 'POST',
                    url: actionUrl,
                    processData: false,
                    contentType: false,
                    data: formData,
                    beforeSend: function () {
                        $('.alert').addClass('hidden').find('.alert-content').html(null);
                        $('#__btnCancelSyncDiscount').attr('disabled', true);
                        $('#__btnSubmitSyncDiscount').attr('disabled', true).html(textSyncing);
                    },
                    success: function (response){
                        $('#__alertSuccessShopeeTable').removeClass('hidden').find('.alert-content').html(response.message);

                        $('#__btnCancelSyncProduct').attr('disabled', false);
                        $('#__btnSubmitSyncProduct').attr('disabled', false).html(textLoadData);

                        $('#__modalSyncDiscount').doModal('close');
                    },
                    error: function (error) {
                        const response = error.responseJSON;
                        let alertMessage = response.message;

                        if (error.status === 422) {
                            const errorFields = Object.keys(response.errors);
                            alertMessage += '<br>';
                            $.each(errorFields, function (index, field) {
                                alertMessage += response.errors[field][0] + '<br>';
                            });
                        }

                        $('#__alertDangerSyncDiscount').removeClass('hidden').find('.alert-content').html(alertMessage);
                        $('#__btnCancelSyncDiscount').attr('disabled', false);
                        $('#__btnSubmitSyncDiscount').attr('disabled', false).html(textLoadData);
                    }
                });

                return false;
            });


            const textYes = "{{ __('translation.yes') }}";
            const textNo = "{{ __('translation.no') }}";
            $(document).on('click', '.shopee-discount-renew-btn', function (event) {
                event.preventDefault();
                
                console.log($("#website_id").find("option:selected").val());
                console.log($(this).data("id"));
                let target = $(this);

                $.ajax({
                    type: 'POST',
                    url: "{{ route('shopee.product.discount.manage_renew') }}",
                    data: {
                        "website": $("#website_id").find("option:selected").val(),
                        "id": target.data("id")
                    },
                    beforeSend: function () {
                    },
                    success: function (response) {
                        if (response.success) {
                            if (target.hasClass('btn-action--yellow')) {
                                target.removeClass('btn-action--yellow');
                                target.addClass('btn-action--green');
                                target.html(textYes);
                            } else if (target.hasClass('btn-action--green')) {
                                target.addClass('btn-action--yellow');
                                target.removeClass('btn-action--green');
                                target.html(textNo);
                            }
                        }
                    },
                    error: function (error) {
                    }
                });
            });
        </script>

    @endpush
</x-app-layout>
