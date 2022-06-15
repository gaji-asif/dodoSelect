<x-app-layout>

    @section('title')
        {{ ucwords(__('translation.shopee_order_analysis')) }}
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

    @if(\App\Models\Role::checkRolePermissions('Can access menu: Shopee - Order'))
        <div class="col-span-12">
            <x-card.card-default>
                <x-card.header>
                    <x-card.title>
                        {{ ucwords(__('translation.shopee order analysis')) }} 
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

                    <hr/>
                    <div id="messageStatus"></div>

                    <div id="chart" class="mb-4"></div>

                    <hr/>
                    <div class="w-full sm:w-4/5 lg:w-3/4 mb-4">
                        <div class="flex flex-col sm:flex-row gap-2">
                            @if (isset($shops))
                            <x-select name="website_id" id="website_id" class="select-shop" style="max-width:200px;">
                                <option value="" disabled selected>
                                    {{ ucwords(__('translation.Select a shop')) }}
                                </option>  
                                <option value="0">{{ ucwords(__('translation.all')) }}</option>
                                @foreach ($shops as $shop)
                                <option value="{{ $shop->id }}">
                                    {{ $shop->shop_name }}
                                </option>
                                @endforeach
                            </x-select>
                            @endif

                            <x-select name="shopee_order_status" id="shopee_order_status" class="ml-2" style="max-width:200px;"> 
                                <option value="" disabled selected>
                                    {{ ucwords(__('translation.Select a status')) }}
                                </option>  
                                <option value="ALL">
                                    {{ ucwords(__('translation.all')) }}
                                </option>  
                                <option value="NOW">
                                    {{ ucwords(__('translation.now')) }}
                                </option>
                                <option value="COMPLETED">
                                    {{ ucwords(__('translation.completed')) }}
                                </option>   
                                <option value="CANCELLED">
                                    {{ ucwords(__('translation.cancelled')) }}
                                </option>  
                                <option value="NOT_CANCELLED">
                                    {{ ucwords(__('translation.not cancelled')) }}
                                </option> 
                            </x-select>

                            <x-select name="shopee_order_interval" id="shopee_order_interval" class="ml-2" style="max-width:200px;">
                                <option value="per_day">
                                    {{ ucwords(__('translation.per day')) }}
                                </option> 
                                <option value="per_week">
                                    {{ ucwords(__('translation.per week')) }}
                                </option>   
                                <option value="per_month">
                                    {{ ucwords(__('translation.per month')) }}
                                </option>
                                <option value="per_year">
                                    {{ ucwords(__('translation.per year')) }}
                                </option>
                            </x-select>
                        </div>
                    </div>

                    <x-alert-success id="__alertSuccessShopeeTable" class="alert hidden"></x-alert-success>
                    <x-alert-danger id="__alertDangerShopeeTable" class="alert hidden"></x-alert-danger>

                    <div class="w-full mt-4 overflow-x-auto">
                        <table class="w-full" id="shopeeOrderHistoryTable">
                            <thead>
                                <tr>
                                    <th class="px-4 py-2 bg-blue-500 text-white">
                                        &nbsp;
                                    </th>
                                    <th class="px-4 py-2 bg-blue-500 text-white">
                                        {{ ucwords(__('translation.shop name')) }}
                                    </th>
                                    <th class="px-4 py-2 bg-blue-500 text-white">
                                        {{ ucwords(__('translation.date')) }}
                                    </th>
                                    <th class="px-4 py-2 bg-blue-500 text-white">
                                        {{ ucwords(__('translation.total orders')) }}
                                    </th>
                                    <th class="px-4 py-2 bg-blue-500 text-white">
                                        {{ ucwords(__('translation.total amount')) }}
                                    </th>
                                    <th class="w-24 px-4 py-2 bg-blue-500 text-white">
                                        {{ ucwords(__('translation.action')) }}
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/apexcharts/3.27.1/apexcharts.min.js"></script>

    <script>
        let selectedWebsiteId = '';
        let selectedStatusId = '';
        let selectedOrderDateInterval = '';
        let shopeeOrderHistoryDatatable = '';
        let th_label_date = "{{ ucwords(__('translation.date')) }}";
        let th_label_shop_name = "{{ ucwords(__('translation.shop_name')) }}";
        let chart_height = 400;
        let datatable_rows_limit = 25;

        $(document).ready(function() {
            updateOrderHistoryChart();
            selectedStatusId = $("#shopee_order_status").val();
            selectedWebsiteId = $("#website_id").val();
            selectedOrderDateInterval = $("#shopee_order_interval").val();
        });


        $(document).on('change', '#website_id', function() {
            selectedWebsiteId = $(this).val();
            if (selectedWebsiteId === "0") {
                datatable_rows_limit = 100;
            } else {
                datatable_rows_limit = 25;
            }
            loadShopeeOrderHistoryTable();
        });


        $(document).on('change', '#shopee_order_status', function() {
            selectedStatusId = $(this).val();
            loadShopeeOrderHistoryTable();
        });


        $(document).on('change', '#shopee_order_interval', function() {
            selectedOrderDateInterval = $(this).val();
            loadShopeeOrderHistoryTable();
        });


        const loadShopeeOrderHistoryTable = () => {
            if (selectedWebsiteId === null || selectedStatusId === null || selectedOrderDateInterval === null) {
                return;
            }
            shopeeProductBoostDatatable = $('#shopeeOrderHistoryTable').DataTable({
                iDisplayLength: datatable_rows_limit,
                bDestroy: true,
                processing: true,
                serverSide: true,
                ajax: {
                    type: 'POST',
                    url: '{{ route("shopee.order.order_history_analysis.data") }}',
                    data: {
                        shopee_id: selectedWebsiteId,
                        status: selectedStatusId,
                        interval: selectedOrderDateInterval
                    }
                },
                dom: '<"#dt-top-toolbar">frt<"#dt-bottom-toolbar"lip><"clear">',
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
                        name: 'shop_name',
                        data: 'shop_name'
                    },
                    {
                        name: 'order_data',
                        data: 'order_data'
                    },
                    {
                        name: 'total_orders_count',
                        data: 'total_orders_count'
                    },
                    {
                        name: 'total_amount',
                        data: 'total_amount'
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
                initComplete: function (settings, json) {
                    initOrderHistoryChart(json.data);
                },
                drawCallback: function(settings) {
                    if (typeof(settings) !== "undefined" && typeof(settings.json) !== "undefined" && typeof(settings.json.data) !== "undefined") {
                        initOrderHistoryChart(settings.json.data);
                    }
                }
            });
        }


        const initOrderHistoryChart = (json_data) => {
            let total_orders = [];
            let total_amount = [];
            let total_amount_line = [];
            let categories = [];
            $.each(json_data, function(i, data) {
                if (selectedWebsiteId === "0") {
                    let key = "";
                    if (typeof(data.order_data) !== "undefined") {
                        key = data.order_data;
                    }
                    if (key.length > 0) {
                        if (typeof(total_orders[key]) === "undefined") {
                            total_orders[key] = data.total_count;
                            total_amount[key] = parseFloat(data.total_amount);
                            total_amount_line[key] = parseFloat(data.total_amount);
                            categories[key] = key;
                        } else {
                            total_orders[key] += data.total_count;
                            total_amount[key] += parseFloat(data.total_amount);
                            total_amount_line[key] += parseFloat(data.total_amount);
                        }
                    }
                } else {
                    total_orders.push(data.total_count);
                    total_amount.push(parseFloat(data.total_amount));
                    total_amount_line.push(parseFloat(data.total_amount));
                    let category = "";
                    
                    if (typeof(data.order_data) !== "undefined") {
                        category += data.order_data;
                    }
                    categories.push(category);
                }
            });

            if (selectedWebsiteId === "0") {
                setTimeout(function() {
                    updateOrderHistoryChart(Object.values(total_orders).reverse(), Object.values(total_amount).reverse(), Object.values(total_amount_line).reverse(), Object.values(categories).reverse());
                }, 500);
            } else {
                setTimeout(function() {
                    updateOrderHistoryChart(total_orders.reverse(), total_amount.reverse(), total_amount.reverse(), categories.reverse());
                }, 500);
            }
        }
            

        const updateOrderHistoryChart = (total_orders = [], total_amount = [], total_amount_line = [], categories = []) => {
            let website = $("#website_id").find("option:selected").text();
            let shopee_order_interval = $("#shopee_order_interval").find("option:selected").text();
            let chart_title = "";
            if (selectedWebsiteId === null || selectedWebsiteId === "" || selectedStatusId === null || selectedStatusId === "") {
                chart_title = 'Order Analysis';
            } else {
                if (selectedWebsiteId === "0") {
                    chart_title = 'Order Analysis For All Shops';
                } else {
                    chart_title = 'Order Analysis For "'+website+'" ('+shopee_order_interval+')'
                }
            }
            var options = {
                series: [
                    {
                        name: 'Total Amount',
                        type: 'column',
                        data: total_amount
                    }, {
                        name: 'Total Orders',
                        type: 'line',
                        data: total_orders
                    }
                ],
                chart: {
                    height: chart_height,
                    type: 'line',
                },
                stroke: {
                    width: [0, 4]
                },
                title: {
                    text: chart_title,
                },
                dataLabels: {
                    enabled: true,
                    enabledOnSeries: [1]
                },
                labels: categories,
                xaxis: {
                    categories: categories,
                },
                yaxis: [
                    {
                        title: {
                            text: 'Total Amount (Baht)',
                        },
                    }, 
                    {
                        opposite: true,
                        title: {
                            text: 'Total Orders'
                        }
                    }
                ],
                legend: {
                    show: true,
                    position: "top",
                }
            };

            $("#chart").html("");
            var chart = new ApexCharts(document.querySelector("#chart"), options);
            chart.render();
        }
    </script>
    @endpush
</x-app-layout>
