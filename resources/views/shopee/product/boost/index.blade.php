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

    @if(\App\Models\Role::checkRolePermissions('Can access menu: Shopee - Product'))
        <div class="col-span-12">

            <x-card.card-default>
                <x-card.header>
                    <x-card.title>
                        {{ ucwords(__('translation.shopee_boost_products')) }} 
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

                            <x-select name="shopee_product_boost_status_type" id="shopee_product_boost_status_type" class="ml-2" style="max-width:200px;"> 
                                <option value="all">
                                    {{ ucwords(__('translation.all')) }}
                                </option>
                                <option value="boosting">
                                    {{ ucwords(__('translation.boosting')) }}
                                </option>
                                <option value="boost_repeat">
                                    {{ ucwords(__('translation.boost_repeat')) }}
                                </option>
                                <option value="boost_once">
                                    {{ ucwords(__('translation.boost_once')) }}
                                </option>
                                <option value="queued">
                                    {{ ucwords(__('translation.queued')) }}
                                </option>
                                <option value="not_queued">
                                    {{ ucwords(__('translation.not_queued')) }}
                                </option>
                            </x-select>

                            <x-select name="type" id="type" class="select__type hidden">
                                <option value="" disabled selected>
                                    - {{ ucwords(__('translation.select_product_type')) }} -
                                </option>
                                <option value="-1">
                                    {{ ucwords(__('translation.all')) }}
                                </option>
                                <option value="ex_variable">
                                    {{ ucwords(__('translation.exclude_variable')) }}
                                </option>
                            </x-select>
                        </div>
                    </div>

                    <div class="">
                        <x-button type="button" color="green" class="btn__refresh_shopee_boosted_products" id="refresh_shopee_boosted_products_btn">
                            <i class="bi bi-arrow-down text-base"></i>
                            <span class="ml-2">
                                {{ ucwords(__('translation.refresh')) }}
                            </span>
                        </x-button> 
                        
                        <x-button type="button" color="green" class="btn__boost_now_shopee_products" id="boost_now_shopee_products_btn">
                            <i class="bi bi-arrow-up text-base"></i>
                            <span class="ml-2">
                                {{ ucwords(__('translation.boost_now')) }}
                            </span>
                            <span class="ml-2" id="boosted_shopee_products_count">
                                (0/0)
                            </span>
                        </x-button>
                    </div>

                    <x-alert-success id="__alertSuccessShopeeTable" class="alert hidden"></x-alert-success>
                    <x-alert-danger id="__alertDangerShopeeTable" class="alert hidden"></x-alert-danger>

                    <div class="w-full mt-4 overflow-x-auto">
                        <table class="w-full" id="shopeeProductBoostTable">
                            <thead>
                                <tr>
                                    <th class="px-4 py-2 bg-blue-500 text-white">
                                        &nbsp;
                                    </th>
                                    <th class="px-4 py-2 bg-blue-500 text-white">
                                        {{ ucwords(__('translation.image')) }}
                                    </th>
                                    <th class="px-4 py-2 bg-blue-500 text-white">
                                        {{ ucwords(__('translation.name')) }}
                                    </th>
                                    <th class="px-4 py-2 bg-blue-500 text-white">
                                        {{ ucwords(__('translation.shop')) }}
                                    </th>
                                    <th class="px-4 py-2 bg-blue-500 text-white">
                                        {{ ucwords(__('translation.type')) }}
                                    </th>
                                    <th class="px-4 py-2 bg-blue-500 text-white">
                                        {{ ucwords(__('translation.price')) }} / {{ ucwords(__('translation.pack')) }}
                                    </th>
                                    <th class="px-4 py-2 bg-blue-500 text-white">
                                        {{ ucwords(__('translation.quantity')) }}
                                    </th>
                                    <th class="w-24 px-4 py-2 bg-blue-500 text-white">
                                        {{ ucwords(__('translation.boost')) }}
                                    </th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </x-card.body>
            </x-card.card-default>
        </div>
    @endif

    @push('bottom_js')
        <script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/jquery-datatables-checkboxes@1.2.12/js/dataTables.checkboxes.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.19/dist/sweetalert2.min.js"></script>

        <script>
            let shopeeProductId = 0;
            let selectedWebsiteId = '';
            let shopeeProductBoostDatatable = '';
            let selectedBoostStatus = '';

            $(document).ready(function() {
                selectedWebsiteId = $("#website_id").find("option:selected").val();
                selectedBoostStatus = $("#shopee_product_boost_status_type").find("option:selected").val();
                if (typeof(selectedWebsiteId) !== "undefined") {
                    loadShopeeProductBoostTable(selectedWebsiteId, selectedBoostStatus);
                    getBoostedProductsCountForSpecificShop();
                }
            });


            $(document).on('change', '#website_id', function() {
                selectedWebsiteId = $(this).val();
                $("#shopee_product_boost_status_type").prop("selectedIndex", 0);
                selectedBoostStatus = $("#shopee_product_boost_status_type").find("option:selected").val();
                loadShopeeProductBoostTable(selectedWebsiteId, selectedBoostStatus);
                getBoostedProductsCountForSpecificShop();
            });


            $(document).on('change', '#shopee_product_boost_status_type', function() {
                selectedBoostStatus = $(this).val();
                loadShopeeProductBoostTable(selectedWebsiteId, selectedBoostStatus);
                if (selectedBoostStatus !== "queued") {
                    getBoostedProductsCountForSpecificShop();
                }
            });


            const loadShopeeProductBoostTable = (websiteId = null) => {
                shopeeProductBoostDatatable = $('#shopeeProductBoostTable').DataTable({
                    bDestroy: true,
                    processing: true,
                    serverSide: true,
                    ajax: {
                        type: 'GET',
                        url: '{{ route("shopee.product.boost.data") }}',
                        data: {
                            website_id: websiteId,
                            boost_status: selectedBoostStatus
                        }
                    },
                    dom: '<"#dt-top-toolbar">frt<"#dt-bottom-toolbar"lip><"clear">',
                    initComplete: function () {
                        if (typeof($("#shopeeProductBoostTable_length").find("select").attr("id")) === "undefined") {
                            $("#shopeeProductBoostTable_length").find("select").attr("id", "shopee_table_page_length");
                        }
                        getBoostingProductsFromTable();
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
                            name: 'image',
                            data: 'image'
                        },
                        {
                            name: 'details',
                            data: 'details'
                        },
                        {
                            name: 'shop_name',
                            data: 'shop_name'
                        },
                        {
                            name: 'type',
                            data: 'type'
                        },
                        {
                            name: 'price',
                            data: 'price'
                        },
                        {
                            name: 'quantity',
                            data: 'quantity'
                        },
                        {
                            name: 'action',
                            data: 'action',
                            orderable: false,
                            className: 'text-center'
                        }
                    ],
                    select : {
                        style: 'multi'
                    },
                    createdRow: function (row, data, dataIndex) {
                        if (data.type == "Simple") {
                            $(row).find('td:eq(0)').attr('class', "simple");
                        } else {
                            $(row).find('td:eq(0)').attr('class', "variable");
                        }
                    },
                });
            }


            let rows_selected = [];
            $(document).on('click', '#boost_now_shopee_products_btn', function() {
                var website_id = $("#website_id").find("option:selected").val();
                if (typeof(website_id) === "undefined" || website_id === null || website_id === "") {
                    alert("Select a website first");
                    return;
                }

                rows_selected = shopeeProductBoostDatatable.column(0).checkboxes.selected();

                var arr = [];
                $.each(rows_selected, function(index, row_id) {
                    arr[index] = row_id;
                });
                if (arr.length === 0) {
                    alert("Please Select At Least 1 Row");
                    return;
                }
                var json_data = JSON.stringify(arr);

                $(this).prop("disabled", true);
                $.ajax({
                    url: '{{ route("shopee.product.boost.set_boosted_products") }}',
                    type: 'post',
                    data: {
                        'website_id': website_id,
                        'product_ids': json_data,
                        '_token': $('meta[name=csrf-token]').attr('content')
                    },
                }).done(function(response) {
                    if (response.success) {
                        loadShopeeProductBoostTable(selectedWebsiteId, selectedBoostStatus);
                        getBoostedProductsCountForSpecificShop();
                        let oldWebsiteId = selectedWebsiteId;
                        setTimeout(function() {
                            if (oldWebsiteId === selectedWebsiteId) {
                                loadShopeeProductBoostTable(selectedWebsiteId, selectedBoostStatus);
                                getBoostedProductsCountForSpecificShop();
                                $('#boost_now_shopee_products_btn').prop("disabled", false);
                            }
                        }, 5000);
                        Swal.fire({
                            icon: 'success',
                            title: 'Succcess',
                            text: response.message
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: response.message
                        });
                        $('#boost_now_shopee_products_btn').prop("disabled", false);
                    }
                });
            });


            $(document).on('click', '#refresh_shopee_boosted_products_btn', function() {
                var website_id = $("#website_id").find("option:selected").val();
                if (typeof(website_id) === "undefined" || website_id === null || website_id === "") {
                    alert("Select a website first");
                    return;
                }
                $(this).prop("disabled", true);
                $.ajax({
                    url: '{{ route("shopee.product.boost.get_boosted_products") }}',
                    type: 'post',
                    data: {
                        'website_id': website_id,
                        '_token': $('meta[name=csrf-token]').attr('content')
                    },
                }).done(function(response) {
                    if (response.success) {
                        loadShopeeProductBoostTable(selectedWebsiteId, selectedBoostStatus);
                        getBoostedProductsCountForSpecificShop();
                        let oldWebsiteId = selectedWebsiteId;
                        setTimeout(function() {
                            if (oldWebsiteId === selectedWebsiteId) {
                                loadShopeeProductBoostTable(selectedWebsiteId, selectedBoostStatus);
                                getBoostedProductsCountForSpecificShop();
                                $('#refresh_shopee_boosted_products_btn').prop("disabled", false);
                            }
                        }, 5000);
                        Swal.fire({
                            icon: 'success',
                            title: 'Succcess',
                            text: response.message
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: response.message
                        });
                        $('#refresh_shopee_boosted_products_btn').prop("disabled", false);
                    }
                });
            });


            const getBoostedProductsCountForSpecificShop = () => {
                var website_id = $("#website_id").find("option:selected").val();
                if (typeof(website_id) === "undefined" || website_id === null || website_id === "") {
                    alert("Select a website first");
                    return;
                }

                $.ajax({
                    url: '{{ route("shopee.product.boost.get_boosted_products_count") }}',
                    type: 'post',
                    data: {
                        'website_id': website_id,
                        '_token': $('meta[name=csrf-token]').attr('content')
                    },
                }).done(function(response) {
                    if (typeof(response.data) !== "undefined" && typeof(response.data.count) !== "undefined" && typeof(response.data.limit) !== "undefined") {
                        $("#boosted_shopee_products_count").html("("+response.data.count+"/"+response.data.limit+")");
                    }
                });
            };


            const getBoostingProductsFromTable = () => {
                let targets = $(".boosting_product_timer");
                $.each(targets, function(index, el) {
                    let product_id = $(el).data("product_id");
                    let expires_at = $(el).data("expires_at");
                    if (typeof(product_id) !== "undefined" && typeof(expires_at) !== "undefined") {
                        updateBoostingProductExpirationTimer(product_id, expires_at);
                    }
                });
            }


            const updateBoostingProductExpirationTimer = (product_id, expires_at) => {
                var x = setInterval(function() {
                    var now = new Date().getTime();
                    var countDownDate = new Date(expires_at).getTime();
                    var distance = countDownDate - now;
                    if (isNaN(distance)) {
                        return;
                    }
                    
                    var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                    var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                    var seconds = Math.floor((distance % (1000 * 60)) / 1000);
                        
                    let html = hours + "h "+ minutes + "m " + seconds + "s ";
                        
                    if (distance < 0) {
                        clearInterval(x);
                        $("#boosting_product_time_"+product_id).find("span").html("Expired");
                    } else {
                        $("#boosting_product_time_"+product_id).find("span").html(html);
                    }
                }, 1000, product_id, expires_at);
            }


            $(document).on('click', '.stop_repeat_btn', function () {
                let conf = confirm("Are you sure you want to remove this product to repeat for boosting?");
                if (!conf) {
                    return;
                }
                let product_id = $(this).data("product_id");
                updateBoostRepeatForQueuedProducts(product_id, "stop");
            });


            $(document).on('click', '.init_repeat_btn', function () {
                let conf = confirm("Are you sure you want to put this product to repeat for boosting?");
                if (!conf) {
                    return;
                }
                let product_id = $(this).data("product_id");
                updateBoostRepeatForQueuedProducts(product_id, "init");
            });

            
            const updateBoostRepeatForQueuedProducts = (product_id, status) => {
                var website_id = $("#website_id").find("option:selected").val();
                if (typeof(website_id) === "undefined" || website_id === null || website_id === "") {
                    alert("Select a website first");
                    return;
                }
                $.ajax({
                    url: '{{ route("shopee.product.update_boost_repeat_for_queued_products") }}',
                    type: 'post',
                    data: {
                        'product_id': product_id,
                        'website_id': website_id,
                        '_token': $('meta[name=csrf-token]').attr('content')
                    },
                }).done(function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Succcess',
                            text: response.message
                        });
                        if (status === "init") {
                            /* Remove "init" button, and "stop" button. */
                            let html = `<button type="button" class="btn-action--red text-xs mt-1 stop_repeat_btn stop_repeat_`+product_id+`_btn" data-product_id="`+product_id+`" title="Stop Repeating Boost"><i class="bi bi-arrow-repeat text-base"></i></button>`;
                            let target = $(".init_repeat_"+product_id+"_btn");
                            target.parent("div").append(html);
                            target.remove();
                        } else {
                            /* Remove "stop" button, and "init" button. */
                            let html = `<button type="button" class="btn-action--green text-xs mt-1 init_repeat_btn init_repeat_`+product_id+`_btn" data-product_id="`+product_id+`" title="Initiate Repeating Boost"><i class="bi bi-arrow-repeat text-base"></i></button>`;
                            let target = $(".stop_repeat_"+product_id+"_btn");
                            target.parent("div").append(html);
                            target.remove();                            
                        }
                        if (["boost_repeat", "boost_once"].includes(selectedBoostStatus)) {
                            loadShopeeProductBoostTable(selectedWebsiteId, selectedBoostStatus);
                        }
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: response.message
                        });
                    }
                });
            }


            $(document).on('click', '.remove_product_from_queue_btn', function () {
                let conf = confirm("Are you sure you want to remove this product from queue?");
                if (!conf) {
                    return;
                }
                let product_id = $(this).data("product_id");
                var website_id = $("#website_id").find("option:selected").val();
                if (typeof(website_id) === "undefined" || website_id === null || website_id === "") {
                    alert("Select a website first");
                    return;
                }
                $.ajax({
                    url: '{{ route("shopee.product.remove_product_from_queue_for_boosting") }}',
                    type: 'post',
                    data: {
                        'product_id': product_id,
                        'website_id': website_id,
                        '_token': $('meta[name=csrf-token]').attr('content')
                    },
                }).done(function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Succcess',
                            text: response.message
                        });
                        loadShopeeProductBoostTable(selectedWebsiteId, selectedBoostStatus);
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: response.message
                        });
                    }
                });
            });

            
        </script>

    @endpush
</x-app-layout>
