<x-app-layout>
    @section('title')
        Company Information Settings
    @endsection

    @push('top_css')
	    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.19/dist/sweetalert2.min.css">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/dropzone@5.9.2/dist/min/dropzone.min.css">
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
                    Company Information Settings
                </x-card.title>
            </x-card.header>
            <x-card.body>
                <div class="pb-3 w-full lg:w-3/5 lg:mx-auto">
                    <form action="{{ route('company-info-settings.update') }}" method="post" id="__formUpdateCompanyInfo">
                        @csrf

                        <div class="mb-5">
                            <div class="mb-2 sm:text-center">
                                <x-label for="company_logo">
                                    {{ __('translation.Company Logo') }}
                                </x-label>
                            </div>
                            <label for="company_logo" class="company_logo__wrapper block w-full sm:w-60 sm:mx-auto mt-2 border border-dashed border-gray-400 rounded-md relative bg-white shadow-sm cursor-pointer">
                                <input type="file" name="company_logo" id="company_logo" class="company_logo__field hidden" accept="image/*">
                                <div class="p-2">
                                    <div class="h-44 flex items-center justify-center">
                                        <img src="{{ $tax_rate_setting->company_logo_url }}" class="company_logo__thumbnail w-full h-auto border border-solid border-gray-300 rounded-md">
                                    </div>
                                </div>
                                <div class="h-7">
                                    <button type="button" class="company_logo__remove_button hidden pt-1 pb-2 w-full border-0 outline-none focus:outline-none bg-transparent text-center text-xs text-red-500 hover:underline">
                                        {{ __('translation.Remove') }}
                                    </button>
                                </div>
                            </label>
                        </div>

                        <div class="mb-5">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-x-8">
                                <div>
                                    <x-label>
                                        Company Name <x-form.required-mark/>
                                    </x-label>
                                    <x-input type="text" name="company_name" id="__company_nameUpdateCompanyInfo" value="{{ $tax_rate_setting->company_name ?? '' }}" />
                                </div>
                                <div>
                                    <x-label>
                                        Tax Number <x-form.required-mark/>
                                    </x-label>
                                    <x-input type="text" name="tax_number" id="__tax_numberUpdateCompanyInfo" value="{{ $tax_rate_setting->tax_number ?? '' }}" />
                                </div>
                                <div>
                                    <x-label>
                                        Phone Number <x-form.required-mark/>
                                    </x-label>
                                    <x-input type="text" name="company_phone" id="__company_phoneUpdateCompanyInfo" value="{{ $tax_rate_setting->company_phone ?? '' }}" />
                                </div>
                                <div>
                                    <x-label>
                                        Contact Person <x-form.required-mark/>
                                    </x-label>
                                    <x-input type="text" name="company_contact_person" id="__company_contact_personUpdateCompanyInfo" value="{{ $tax_rate_setting->company_contact_person ?? '' }}" />
                                </div>
                                <div class="sm:col-span-2">
                                    <x-label>
                                        Address <x-form.required-mark/>
                                    </x-label>
                                    <x-textarea name="company_address" id="__company_addressUpdateCompanyInfo" rows="3">{{ $tax_rate_setting->company_address ?? '' }}</x-textarea>
                                </div>
                                <div>
                                    <x-label>
                                        Province <x-form.required-mark/>
                                    </x-label>
                                    <x-input type="text" name="company_province" id="__company_provinceUpdateCompanyInfo" value="{{ $tax_rate_setting->company_province ?? '' }}" />
                                </div>
                                <div>
                                    <x-label>
                                        District <x-form.required-mark/>
                                    </x-label>
                                    <x-input type="text" name="company_district" id="__company_districtUpdateCompanyInfo" value="{{ $tax_rate_setting->company_district ?? '' }}" />
                                </div>
                                <div>
                                    <x-label>
                                        Sub-District <x-form.required-mark/>
                                    </x-label>
                                    <x-input type="text" name="company_sub_district" id="__company_sub_districtUpdateCompanyInfo" value="{{ $tax_rate_setting->company_sub_district ?? '' }}" />
                                </div>
                                <div>
                                    <x-label>
                                        Postal Code <x-form.required-mark/>
                                    </x-label>
                                    <x-input type="text" name="company_postcode" id="__company_postcodeUpdateCompanyInfo" value="{{ $tax_rate_setting->company_postcode ?? '' }}" />
                                </div>
                            </div>
                        </div>

                        <div class="text-center">
                            <x-button type="submit" color="blue" id="__btnSubmitUpdateCompanyInfo">
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
        <script src="https://cdn.jsdelivr.net/npm/dropzone@5.9.2/dist/min/dropzone.min.js"></script>

        <script src="{{ asset('pages/admin/settings/company_info_settings/form_update.js?_=' . rand()) }}"></script>
    @endpush
</x-app-layout>
