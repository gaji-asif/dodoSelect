<x-app-layout>
    @section('title', 'Dashboard')

    @if (session('roleName') != 'dropshipper')
        <div class="w-full overflow-hidden col-span-12 md:col-span-4">
            <div class="flex flex-row w-full rounded-md p-4 bg-white">
                <div class="flex items-center justify-center flex-shrink-0 h-16 w-16 rounded-lg bg-blue-100 text-blue-500">
                    <i class="bi bi-cart3 text-3xl"></i>
                </div>
                <div class="flex flex-col justify-center flex-grow ml-4">
                    <div class="text-md text-gray-500">
                        {{ __('translation.Total Orders Today') }}
                    </div>
                    <div class="font-bold text-xl">
                        <span id="__countOrdersToday">
                            <svg class="animate-spin h-5 w-5 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="w-full overflow-hidden col-span-12 md:col-span-4">
            <div class="flex flex-row w-full rounded-md p-4 bg-white">
                <div class="flex items-center justify-center flex-shrink-0 h-16 w-16 rounded-lg bg-blue-100 text-blue-500">
                    <i class="bi bi-minecart-loaded text-3xl"></i>
                </div>
                <div class="flex flex-col justify-center flex-grow ml-4">
                    <div class="text-md text-gray-500">
                        {{ __('translation.Orders To Process') }}
                    </div>
                    <div class="font-bold text-xl">
                        <span id="__countOrdersToProcess">
                            <svg class="animate-spin h-5 w-5 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="w-full overflow-hidden col-span-12 md:col-span-4">
            <div class="flex flex-row w-full rounded-md p-4 bg-white">
                <div class="flex items-center justify-center flex-shrink-0 h-16 w-16 rounded-lg bg-blue-100 text-blue-500">
                    <i class="bi bi-truck text-3xl"></i>
                </div>
                <div class="flex flex-col justify-center flex-grow ml-4">
                    <div class="text-md text-gray-500">
                        {{ __('translation.Orders To Ship') }}
                    </div>
                    <div class="font-bold text-xl">
                        <span id="__countShipmentToShip">
                            <svg class="animate-spin h-5 w-5 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="w-full overflow-hidden col-span-12 md:col-span-4">
            <div class="flex flex-row w-full rounded-md p-4 bg-white">
                <div class="flex items-center justify-center flex-shrink-0 h-16 w-16 rounded-lg bg-blue-100 text-blue-500">
                    <i class="bi bi-box-arrow-down-right text-3xl"></i>
                </div>
                <div class="flex flex-col justify-center flex-grow ml-4">
                    <div class="text-md text-gray-500">
                        {{ __('translation.Low Stock Total') }}
                    </div>
                    <div class="font-bold text-xl">
                        <span id="__countLowStock">
                            <svg class="animate-spin h-5 w-5 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="w-full overflow-hidden col-span-12 md:col-span-4">
            <div class="flex flex-row w-full rounded-md p-4 bg-white">
                <div class="flex items-center justify-center flex-shrink-0 h-16 w-16 rounded-lg bg-blue-100 text-blue-500">
                    <i class="bi bi-box-arrow-down text-3xl"></i>
                </div>
                <div class="flex flex-col justify-center flex-grow ml-4">
                    <div class="text-md text-gray-500">
                        {{ __('translation.Out of Stock Total') }}
                    </div>
                    <div class="font-bold text-xl">
                        <span id="__countOutOfStock">
                            <svg class="animate-spin h-5 w-5 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="w-full overflow-hidden col-span-12 md:col-span-4">
            <div class="flex flex-row w-full rounded-md p-4 bg-white">
                <div class="flex items-center justify-center flex-shrink-0 h-16 w-16 rounded-lg bg-blue-100 text-blue-500">
                    <i class="bi bi-folder-x text-3xl"></i>
                </div>
                <div class="flex flex-col justify-center flex-grow ml-4">
                    <div class="text-md text-gray-500">
                        {{ __('translation.Defect Stock Total') }}
                    </div>
                    <div class="font-bold text-xl">
                        <span id="__countDefectStock">
                            <svg class="animate-spin h-5 w-5 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <style type="text/css">
            .chart {
                height: 400px;
                width:600px;
                margin: 0 auto;
                overflow-y: hidden;
            }
        </style>

        <x-card title="Daily Summary" md="8">
            <div class="overflow-x-auto">
                <div id="lineChartContainer" class="chart"></div>
            </div>
        </x-card>

        <div id="__templateProductItem" class="hidden">
            <div class="flex flex-row w-full shadow rounded-lg p-4 mb-4 bg-white">
                <div class="flex items-center justify-center flex-shrink-0 h-16 w-16 rounded-lg bg-blue-100 text-blue-500">
                    <img class="product-image w-full h-full object-cover rounded-lg" src="###">
                </div>
                <div class="flex flex-col justify-center flex-grow ml-4">
                    <div class="text-gray-700 font-bold">
                        {product_name}
                    </div>
                    <div class="text-blue-600 font-bold">
                        {product_code}
                    </div>
                    <div class="font-bold">
                        <span>
                            Qty : {product_qty} {product_qty_change}
                        </span>
                    </div>
                    <div class="text-xs">
                        {{ __('translation.Created at') }} : {datetime}
                    </div>
                    <div class="text-xs">
                        {{ __('translation.User') }} : {seller_name}
                    </div>
                </div>
            </div>
        </div>

        <div class="w-full col-span-12 md:col-span-4">
            <div class="flex flex-row w-full rounded-md p-4 mb-2 bg-white">
                <div class="flex flex-col justify-center flex-grow ml-4">
                    <div class="font-bold text-xl">
                        {{ __('translation.Last Changes') }}
                    </div>
                </div>
            </div>
            <div id="__lastChangeProductWrapper" class="scroll-with-slim max-h-[40rem] py-2">
                <div class="w-full flex items-center justify-center p-6">
                    <svg class="animate-spin h-5 w-5 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="w-full col-span-12 md:col-span-4">
            <div class="flex flex-row w-full rounded-md p-4 mb-2 bg-white">
                <div class="flex flex-col justify-center flex-grow ml-4">
                    <div class="font-bold text-xl">
                        {{ __('translation.Top 5 Highest Stock') }}
                    </div>
                </div>
            </div>
            <div id="__highestStockProductWrapper" class="scroll-with-slim max-h-[40rem] py-2">
                <div class="w-full flex items-center justify-center p-6">
                    <svg class="animate-spin h-5 w-5 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="w-full col-span-12 md:col-span-4">
            <div class="flex flex-row w-full rounded-md p-4 mb-2 bg-white">
                <div class="flex flex-col justify-center flex-grow ml-4">
                    <div class="font-bold text-xl">
                        {{ __('translation.Top 5 Added Stock') }}
                    </div>
                </div>
            </div>
            <div id="__latestStockProductAddWrapper" class="scroll-with-slim max-h-[40rem] py-2">
                <div class="w-full flex items-center justify-center p-6">
                    <svg class="animate-spin h-5 w-5 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="w-full col-span-12 md:col-span-4">
            <div class="flex flex-row w-full rounded-md p-4 mb-2 bg-white">
                <div class="flex flex-col justify-center flex-grow ml-4">
                    <div class="font-bold text-xl">
                        {{ __('translation.Top 5 Removed Stock') }}
                    </div>
                </div>
            </div>
            <div id="__latestStockProductRemoveWrapper" class="scroll-with-slim max-h-[40rem] py-2">
                <div class="w-full flex items-center justify-center p-6">
                    <svg class="animate-spin h-5 w-5 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </div>
            </div>
        </div>
    @endif


    @push('bottom_js')
        <script src="https://cdnjs.cloudflare.com/ajax/libs/apexcharts/3.27.1/apexcharts.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.22.2/moment.min.js"></script>
        <script src="{{ asset('pages/dashboard.js?_=' . rand()) }}"></script>

        @if (session('roleName') != 'dropshipper')
            <script src="{{ asset('pages/seller/dashboard/counter.js?_=' . rand()) }}" defer></script>
            <script src="{{ asset('pages/seller/dashboard/data-last-change-products.js?_=' . rand()) }}" defer></script>
            <script src="{{ asset('pages/seller/dashboard/data-highest-stock-products.js?_=' . rand()) }}" defer></script>
            <script src="{{ asset('pages/seller/dashboard/data-latest-stock-add.js?_=' . rand()) }}" defer></script>
            <script src="{{ asset('pages/seller/dashboard/data-latest-stock-remove.js?_=' . rand()) }}" defer></script>
        @endif

        {{-- @if(isset($data_add_to_stock) || isset($data_remove_from_stock)) --}}
            <script type="text/javascript">
                window.Apex = {
                    chart: {
                        foreColor: "#222",
                        toolbar: {
                            show: false
                        }
                    },
                    colors: [ "#17ead9", "#f1bcbc","#f02fc2"],
                    stroke: {
                        width: 3
                    },
                    dataLabels: {
                        enabled: true
                    },
                    grid: {
                        borderColor: "#40475D"
                    },
                    xaxis: {
                        labels: {
                            datetimeUTC: false
                        },
                        axisTicks: {
                            color: "#333"
                        },
                        axisBorder: {
                            color: "#333"
                        }
                    },
                    fill: {
                        type: "gradient",
                        gradient: {
                            gradientToColors: ["#fff", "#fff", "#fff"]
                        }
                    },
                    tooltip: {
                        theme: "dark",
                        x: {
                            formatter: function (val) {
                                return moment(val);
                            }
                        }
                    },
                    yaxis: {
                        decimalsInFloat: 0,
                        opposite: true,
                        labels: {
                            offsetX: -10
                        }
                    }
                };

                var options = {
                    series: [
                        {
                            name: 'Add',
                            data: <?php echo json_encode(collect($stock_logs_add_data)->pluck('quantity')->toArray()) ?>
                        },
                        {
                            name: 'Remove',
                            data: <?php echo json_encode(collect($stock_logs_remove_data)->pluck('quantity')->toArray()) ?>
                        }
                    ],
                    chart: {
                        height: 520,
                        type: 'area'
                    },
                    dataLabels: {
                        enabled: false
                    },
                    stroke: {
                        curve: 'smooth'
                    },
                    xaxis: {
                        type: 'datetime',
                        categories: <?php echo json_encode(collect($stock_logs_add_data)->pluck('datetime')->toArray()) ?>
                    },
                    tooltip: {
                        theme: "dark",
                        x: {
                            formatter: function (val) {
                                return moment(new Date(val)).format("HH:mm:ss");
                            }
                        }
                    },
                    title: {
                        text: "Daily Stock",
                        align: "left",
                        style: {
                            fontSize: "12px"
                        }
                    },
                    subtitle: {
                        text: "Quantity",
                        floating: true,
                        align: "right",
                        offsetY: 0,
                        style: {
                            fontSize: "22px"
                        }
                    },
                    legend: {
                        show: true,
                        floating: true,
                        horizontalAlign: "left",
                        onItemClick: {
                            toggleDataSeries: false
                        },
                        position: "top",
                        offsetY: -33,
                        offsetX: 60
                    }
                };
                var chart = new ApexCharts(document.querySelector("#lineChartContainer"), options);
                chart.render();
            </script>
        {{-- @endif --}}
    @endpush

</x-app-layout>
