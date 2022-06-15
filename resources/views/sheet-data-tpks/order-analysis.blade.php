<x-app-layout>

    @section('title')
        {{ __('translation.Order Analysis') }} - {{ __('translation.TPK Packing Data') }}
    @endsection


    @if(\App\Models\Role::checkRolePermissions('Can access menu: TPK Packing Data'))
        <div class="col-span-12">
            <x-card.card-default>
                <x-card.header>
                    <x-card.title>
                        {{ __('translation.Order Analysis') }} - {{ __('translation.TPK Packing Data') }}
                    </x-card.title>
                </x-card.header>
                <x-card.body>

                    <div class="mb-6">
                        <div id="order-analysis-chart"></div>
                    </div>

                    <div class="mb-6 w-full flex flex-col items-center justify-center gap-2 lg:flex-row">
                        <div class="w-full flex flex-col sm:flex-row gap-2">
                            <div class="w-full sm:w-1/2">
                                <x-form.select id="__shopFilter">
                                    <option value="-1" selected>
                                        {{ __('translation.All Shop') }}
                                    </option>
                                </x-form.select>
                            </div>
                            <div class="w-full sm:w-1/2">
                                <x-form.input type="text" id="__date_rangeFilter" value="{{ date('Y') . '01-01' . ' to ' . date('Y') . '-12-31' }}" />
                            </div>
                        </div>
                        <div class="w-full flex flex-col sm:flex-row gap-2">
                            <div class="w-full sm:w-1/2">
                                <x-form.select id="__intervalFilter">
                                    <option value="" disabled>
                                        - {{ __('translation.Select Interval') }} -
                                    </option>
                                    <option value="per_day" selected>
                                        {{ __('translation.Per Day') }}
                                    </option>
                                    <option value="per_week">
                                        {{ __('translation.Per Week') }}
                                    </option>
                                    <option value="per_month">
                                        {{ __('translation.Per Month') }}
                                    </option>
                                    <option value="per_year">
                                        {{ __('translation.Per Year') }}
                                    </option>
                                </x-form.select>
                            </div>
                            <div class="w-full sm:w-1/2">
                                <x-form.select id="__channelFilter">
                                    <option value="" disabled>
                                        - {{ __('translation.Select Channel') }} -
                                    </option>
                                    <option value="all" selected>
                                        {{ __('translation.All Channel') }}
                                    </option>
                                    @foreach ($channels as $channel)
                                        <option value="{{ $channel->channel }}">
                                            @if (empty($channel->channel))
                                                N/A
                                            @else
                                                {{ $channel->channel }}
                                            @endif
                                        </option>
                                    @endforeach
                                </x-form.select>
                            </div>
                        </div>
                    </div>
                    <div class="w-full overflow-x-auto">
                        <table class="w-full" id="__orderAnalysisTable">
                            <thead>
                                <tr>
                                    <th class="px-2 py-4 bg-blue-500 text-white">
                                        #
                                    </th>
                                    <th class="px-2 py-4 bg-blue-500 text-white">
                                        {{ __('translation.Shop Name') }}
                                    </th>
                                    <th class="px-2 py-4 bg-blue-500 text-white">
                                        {{ __('translation.Date') }}
                                    </th>
                                    <th class="px-2 py-4 bg-blue-500 text-white">
                                        {{ __('translation.Total Orders') }}
                                    </th>
                                    <th class="px-2 py-4 bg-blue-500 text-white">
                                        {{ __('translation.Total Amount') }}
                                    </th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </x-card.body>
            </x-card.card-default>
        </div>

    @endif


    @push('top_css')
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr@4.6.9/dist/flatpickr.min.css">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/apexcharts@3.33.1/dist/apexcharts.min.css">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css">
    @endpush

    @push('bottom_js')
        <script src="https://cdn.jsdelivr.net/npm/moment@2.29.1/moment.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/flatpickr@4.6.9/dist/flatpickr.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/apexcharts@3.33.1/dist/apexcharts.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>
        <script src="{{ asset('pages/seller/sheet-data-tpks/order-analysis/table.js?_=' . rand()) }}" defer></script>
        <script src="{{ asset('pages/seller/sheet-data-tpks/order-analysis/chart.js?_=' . rand()) }}" defer></script>
    @endpush

</x-app-layout>
