<x-app-layout>
    @section('title')
        Tax Rate Settings
    @endsection

    @push('top_css')
	    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.19/dist/sweetalert2.min.css">
    @endpush

    <div class="col-span-12">
        <x-card.card-default>
            <x-card.body>
                @include('settings.menu')
            </x-card.body>
        </x-card.card-default>

        <x-card.card-default>
            <x-card.header>
                <x-card.title>
                    Tax Rate Settings
                </x-card.title>
            </x-card.header>
            <x-card.body>
                <div class="pb-3 w-full sm:w-2/5 md:w-1/3 mx-auto">
                    <form action="{{ route('tax-rate-settings.update') }}" method="post" id="__formUpdateTaxRate">
                        @csrf

                        <div class="mb-5">
                            <x-label>
                                Tax Name <x-form.required-mark/>
                            </x-label>
                            <div class="flex flex-row items-center justify-between">
                                <x-input type="text" name="tax_name" value="{{ $tax_rate_setting->tax_name ?? 'VAT' }}" />
                            </div>
                        </div>
                        <div class="mb-5">
                            <x-label>
                                Tax Rate <x-form.required-mark/>
                            </x-label>
                            <div class="flex flex-row items-center justify-between">
                                <div class="w-4/5 mr-4">
                                    <x-input type="number" name="tax_rate" step="0.01" value="{{ $tax_rate_setting->tax_rate ?? 0 }}" />
                                </div>
                                <div class="w-1/5">
                                    <x-input type="text" class="text-center" value="%" readonly />
                                </div>
                            </div>
                        </div>
                        <div class="text-center">
                            <x-button type="submit" color="blue" id="__btnSubmitUpdateTaxRate">
                                Update Data
                            </x-button>
                        </div>
                    </form>
                </div>
            </x-card.body>
        </x-card.card-default>
    </div>

    @push('bottom_js')
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.19/dist/sweetalert2.min.js"></script>
        <script src="{{ asset('pages/admin/settings/tax_rate_settings/index.js?_=' . rand()) }}"></script>
    @endpush
</x-app-layout>