<x-app-layout>

    @section('title')
        {{ __('translation.Create Custom Order') }}
    @endsection

    @push('top_css')
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css">
    @endpush


    <div class="col-span-12">
        <x-card.card-default>
            <x-card.header>
                <x-card.back-button href="{{ route('custom-order.index') }}" />
                <x-card.title>
                    {{ __('translation.Create Custom Order') }}
                </x-card.title>
            </x-card.header>
            <x-card.body>

                <x-alert-success class="mb-6 alert hidden" id="__alertSuccess">
                    <div id="__alertSuccessContent"></div>
                </x-alert-success>

                <x-alert-danger class="mb-6 alert hidden" id="__alertDanger">
                    <div id="__alertDangerContent"></div>
                </x-alert-danger>

                <form action="#" method="post" id="__formCreateCustomOrder" enctype="multipart/form-data">
                    @csrf

                    <div class="mb-10">
                        <div class="flex flex-row items-center justify-between mb-3">
                            <h2 class="block whitespace-nowrap text-gray-600 text-base font-bold">
                                {{ __('translation.Order Info') }}
                            </h2>
                            <hr class="w-full ml-3 relative -top-1 border border-r-0 border-b-0 border-l-0 border-gray-300">
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-7 gap-4 sm:gap-x-8">
                            <div class="sm:col-span-2 lg:col-span-3">
                                <x-label for="shop_id">
                                    {{ __('translation.Shop') }} <x-form.required-mark/>
                                </x-label>
                                <x-select name="shop_id" id="shop_id" style="width: 100%">
                                    <option value="" selected disabled>
                                        {{ '- '.  __('translation.Select Shop') .' -' }}
                                    </option>
                                </x-select>
                            </div>
                        </div>
                    </div>
                    <div class="mb-10">
                        <div class="flex flex-row items-center justify-between mb-3">
                            <h2 class="block whitespace-nowrap text-gray-600 text-base font-bold">
                                {{ __('translation.Channel Info') }}
                            </h2>
                            <hr class="w-full ml-3 relative -top-1 border border-r-0 border-b-0 border-l-0 border-gray-300">
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-x-8">
                            <div>
                                <x-label for="channel_id">
                                    {{ __('translation.Channel Name') }} <x-form.required-mark/>
                                </x-label>
                                <div class="w-full mt-2 grid grid-cols-6 gap-4">
                                    @foreach ($channels as $idx => $channel)
                                        @if ($idx == 0)
                                            <label for="channel_{{ $channel->id }}" class="channel-item block w-10 h-10 p-1 rounded-md border border-solid border-yellow-500 hover:border-yellow-500 cursor-pointer transition duration-300" data-name="{{ $channel->name }}">
                                                <input type="radio" name="channel_id" id="channel_{{ $channel->id }}" value="{{ $channel->id }}" class="hidden" checked>
                                                <div class="w-full h-full rounded-md bg-no-repeat bg-cover" style="background-image: url('{{ $channel->image_url }}')"></div>
                                            </label>
                                        @else
                                            <label for="channel_{{ $channel->id }}" class="channel-item block w-10 h-10 p-1 rounded-md border border-solid border-gray-300 hover:border-yellow-500 cursor-pointer transition duration-300" data-name="{{ $channel->name }}">
                                                <input type="radio" name="channel_id" id="channel_{{ $channel->id }}" value="{{ $channel->id }}" class="hidden">
                                                <div class="w-full h-full rounded-md bg-no-repeat bg-cover" style="background-image: url('{{ $channel->image_url }}')"></div>
                                            </label>
                                        @endif
                                    @endforeach
                                </div>
                                <div class="mt-3">
                                    <span class="text-gray-500">Selected Channel : </span>
                                    <span class="font-bold text-yellow-500" id="__selectedChannelOutput">
                                        {{ $channels[0]->name }}
                                    </span>
                                </div>
                            </div>
                            <div>
                                <x-label for="contact_name">
                                    {{ __('translation.Channel ID') }} <x-form.required-mark/>
                                </x-label>
                                <x-input type="text" name="contact_name" id="contact_name" />
                            </div>
                        </div>
                    </div>
                    <div class="mb-10">
                        <div class="flex flex-row items-center justify-between mb-3">
                            <h2 class="block whitespace-nowrap text-gray-600 text-base font-bold">
                                {{ __('translation.Customer Info') }}
                                <small class="ml-2 text-gray-500">
                                    {{ __('translation.Search by phone number first') }}
                                </small>
                            </h2>
                            <hr class="w-full ml-3 relative -top-1 border border-r-0 border-b-0 border-l-0 border-gray-300">
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-x-8">
                            <div>
                                <x-label for="search_contact_phone">
                                    {{ __('translation.Search Phone Number') }} <x-form.required-mark/>
                                </x-label>
                                <div class="flex flex-row items-center justify-between">
                                    <x-input type="text" id="search_contact_phone" class="rounded-tr-none rounded-br-none" />
                                    <x-button type="button" color="blue" class="rounded-tl-none rounded-bl-none relative" id="__btnContactPhone">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-search" viewBox="0 0 16 16">
                                            <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z"/>
                                        </svg>
                                    </x-button>
                                </div>
                                <div class="mt-2 hidden" id="__fetchCustomerResultMessage">
                                    <span class="font-bold"></span>
                                </div>
                            </div>
                            <div class="sm:col-span-2 grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-x-8">
                                <div class="hidden" id="__customerNameWrapper">
                                    <x-label for="customer_name">
                                        {{ __('translation.Customer Name') }} <x-form.required-mark/>
                                    </x-label>
                                    <x-input type="text" name="customer_name" id="customer_name" />
                                </div>
                                <div class="hidden" id="__contactPhoneWrapper">
                                    <x-label for="contact_phone">
                                        {{ __('translation.Phone Number') }} <x-form.required-mark/>
                                    </x-label>
                                    <x-input type="text" name="contact_phone" id="contact_phone" />
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mb-10">
                        <div class="flex flex-row items-center justify-between mb-3">
                            <h2 class="block whitespace-nowrap text-gray-600 text-base font-bold">
                                {{ __('translation.Products') }}
                                <small class="ml-2 text-gray-500">
                                    {{ __('translation.You can add many products') }}
                                </small>
                            </h2>
                            <hr class="w-full ml-3 relative -top-1 border border-r-0 border-b-0 border-l-0 border-gray-300">
                        </div>
                        <div>
                            <div id="__productListWrapper"></div>
                            <div class="text-center">
                                <x-button type="button" color="green" id="__btnAddProductItem">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="w-4 h-4 bi bi-plus" viewBox="0 0 16 16">
                                        <path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4z"/>
                                    </svg>
                                    <span class="ml-2">
                                        {{ __('translation.Add Product') }}
                                    </span>
                                </x-button>
                            </div>
                        </div>
                    </div>
                    <div class="mb-10">
                        <div class="flex flex-row items-center justify-between mb-3">
                            <h2 class="block whitespace-nowrap text-gray-600 text-base font-bold">
                                {{ __('translation.Shipping Methods') }}
                            </h2>
                            <hr class="w-full ml-3 relative -top-1 border border-r-0 border-b-0 border-l-0 border-gray-300">
                        </div>
                        <div class="w-full grid grid-cols-1 sm:grid-cols-5 lg:grid-cols-10 gap-4 sm:gap-x-8 lg:pr-8">
                            <div class="sm:col-span-3">
                                <x-label for="shipping_name">
                                    {{ __('translation.Name') }} <x-form.required-mark/>
                                </x-label>
                                <x-input type="text" name="shipping_name" id="shipping_name" />
                            </div>
                            <div class="sm:col-span-2">
                                <x-label for="shipping_cost">
                                    {{ __('translation.Cost') }} <x-form.required-mark/>
                                </x-label>
                                <x-input type="number" name="shipping_cost" id="shipping_cost" step="0.01" />
                            </div>
                        </div>
                    </div>
                    <div class="mb-10">
                        <div class="flex flex-row items-center justify-between mb-3">
                            <h2 class="block whitespace-nowrap text-gray-600 text-base font-bold">
                                {{ __('translation.Cart Totals') }}
                            </h2>
                            <hr class="w-full ml-3 relative -top-1 border border-r-0 border-b-0 border-l-0 border-gray-300">
                        </div>
                        <div class="w-full lg:w-1/2 lg:mx-auto">
                            <table class="w-full -mt-1">
                                <tbody>
                                    <tr>
                                        <td class="pr-3 py-1">
                                            Sub Total
                                        </td>
                                        <td class="py-1">
                                            <span class="font-bold">
                                                {{ currency_symbol('THB') }}
                                            </span>
                                        </td>
                                        <td class="pl-3 py-1 text-right">
                                            <span class="font-bold" id="__subTotalCurrency">
                                                0
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="pr-3 py-1">
                                            Shipping Cost
                                        </td>
                                        <td class="py-1">
                                            <span class="font-bold">
                                                {{ currency_symbol('THB') }}
                                            </span>
                                        </td>
                                        <td class="pl-3 py-1 text-right">
                                            <span class="font-bold" id="__shippingCostCurrency">
                                                0
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="3" class="pt-1 border border-dashed border-r-0 border-b-0 border-l-0 border-gray-400"></td>
                                    </tr>
                                    <tr>
                                        <td class="pr-3 py-1">
                                            Total Amount
                                        </td>
                                        <td class="py-1">
                                            <span class="font-bold">
                                                {{ currency_symbol('THB') }}
                                            </span>
                                        </td>
                                        <td class="pl-3 py-1 text-right">
                                            <span class="font-bold" id="__grandTotalCurrency">
                                                0
                                            </span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="text-center mb-2">
                        <x-button type="button" color="gray" class="mr-1" id="__btnCancelCreateOrder">
                            {{ __('translation.Cancel') }}
                        </x-button>
                        <x-button type="submit" color="blue" id="__btnSubmitCreateOrder">
                            {{ __('translation.Create Order') }}
                        </x-button>
                    </div>
                </form>
            </x-card.body>
        </x-card.card-default>
    </div>


    <div id="__productItemTemplateWrapper" class="hidden">
        <div class="product-item--wrapper w-full p-5 mb-5 rounded-md bg-gray-50" id="product_item_wrapper__{productId}">
            <button type="button" class="product_item__remove_button float-left -mt-7 -ml-7 mb-3 w-7 h-7 border-0 rounded-full outline-none focus:outline-none shadow-md bg-red-500 hover:bg-opacity-80 transition-all duration-300" title="{{ __('translation.Remove Product') }}" data-id="{productId}">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="w-6 h-6 text-white bi bi-x" viewBox="0 0 16 16">
                    <path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708z"/>
                </svg>
            </button>
            <div class="clear-both mb-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-x-8 mb-4">
                    <div class="">
                        <x-label for="product_name">
                            {{ __('translation.Product Name') }} <x-form.required-mark/>
                        </x-label>
                        <x-input type="text" name="product_name[]" />
                    </div>
                    <div class="sm:col-span-1 grid grid-cols-2 gap-4 sm:gap-x-8">
                        <div>
                            <x-label for="product_price">
                                {{ __('translation.Price') }} <x-form.required-mark/>
                            </x-label>
                            <x-input type="number" name="product_price[]" id="product_price__{productId}" step="0.01" class="product_price" data-id="{productId}" />
                        </div>
                        <div>
                            <x-label for="quantity">
                                {{ __('translation.Quantity') }} <x-form.required-mark/>
                            </x-label>
                            <x-input type="number" name="quantity[]" id="product_quantity__{productId}" class="product_quantity" data-id="{productId}" />
                        </div>
                    </div>
                </div>
                <div class="mb-4">
                    <x-label for="product_description">
                        {{ __('translation.Description') }}
                    </x-label>
                    <x-form.textarea name="product_description[]" rows="3"></x-form.textarea>
                </div>
                <div>
                    <x-label for="images">
                        {{ __('translation.Images') }}
                        <small class="text-gray-600 text-xs ml-1">
                            {{ __('translation.jpg/jpeg/png') }}
                        </small>
                    </x-label>
                    <div class="w-full lg:w-3/5 mt-1 grid grid-cols-2 sm:grid-cols-4 gap-4">
                        <label for="product_image_one_{productId}" class="product_image__wrapper_{productId} block w-full mt-2 border border-dashed border-gray-400 rounded-md relative bg-white shadow-sm cursor-pointer">
                            <input type="file" name="product_image_one[]" id="product_image_one_{productId}" class="product_image__field hidden" data-id="{productId}">
                            <div class="p-2">
                                <div class="h-24">
                                    <img src="#" class="product_image__thumbnail w-full h-full border border-solid border-gray-300 rounded-md hidden">
                                </div>
                            </div>
                            <div class="h-7">
                                <button type="button" class="product_images__remove_button hidden pt-1 pb-2 w-full border-0 outline-none focus:outline-none bg-transparent text-center text-xs text-red-500 hover:underline" data-id="{productId}">
                                    {{ __('translation.Remove') }}
                                </button>
                            </div>
                        </label>
                        <label for="product_image_two_{productId}" class="product_image__wrapper_{productId} block w-full mt-2 border border-dashed border-gray-400 rounded-md relative bg-white shadow-sm cursor-pointer">
                            <input type="file" name="product_image_two[]" id="product_image_two_{productId}" class="product_image__field hidden" data-id="{productId}">
                            <div class="p-2">
                                <div class="h-24">
                                    <img src="#" class="product_image__thumbnail w-full h-full border border-solid border-gray-300 rounded-md hidden">
                                </div>
                            </div>
                            <div class="h-7">
                                <button type="button" class="product_images__remove_button hidden pt-1 pb-2 w-full border-0 outline-none focus:outline-none bg-transparent text-center text-xs text-red-500 hover:underline" data-id="{productId}">
                                    {{ __('translation.Remove') }}
                                </button>
                            </div>
                        </label>
                        <label for="product_image_three_{productId}" class="product_image__wrapper_{productId} block w-full mt-2 border border-dashed border-gray-400 rounded-md relative bg-white shadow-sm cursor-pointer">
                            <input type="file" name="product_image_three[]" id="product_image_three_{productId}" class="product_image__field hidden" data-id="{productId}">
                            <div class="p-2">
                                <div class="h-24">
                                    <img src="#" class="product_image__thumbnail w-full h-full border border-solid border-gray-300 rounded-md hidden">
                                </div>
                            </div>
                            <div class="h-7">
                                <button type="button" class="product_images__remove_button hidden pt-1 pb-2 w-full border-0 outline-none focus:outline-none bg-transparent text-center text-xs text-red-500 hover:underline" data-id="{productId}">
                                    {{ __('translation.Remove') }}
                                </button>
                            </div>
                        </label>
                        <label for="product_image_four_{productId}"  class="product_image__wrapper_{productId} block w-full mt-2 border border-dashed border-gray-400 rounded-md relative bg-white shadow-sm cursor-pointer">
                            <input type="file" name="product_image_four[]" id="product_image_four_{productId}" class="product_image__field hidden" data-id="{productId}">
                            <div class="p-2">
                                <div class="h-24">
                                    <img src="#" class="product_image__thumbnail w-full h-full border border-solid border-gray-300 rounded-md hidden">
                                </div>
                            </div>
                            <div class="h-7">
                                <button type="button" class="product_images__remove_button hidden pt-1 pb-2 w-full border-0 outline-none focus:outline-none bg-transparent text-center text-xs text-red-500 hover:underline" data-id="{productId}">
                                    {{ __('translation.Remove') }}
                                </button>
                            </div>
                        </label>
                    </div>
                    <div class="mt-4">
                        <small class="text-blue-500 text-xs">
                            {{ __('translation.** Max filesize for each image is 5 MB') }}
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <x-modal.modal-small class="modal-hide" id="__modalCancelCreate">
        <x-modal.header>
            <x-modal.title>
                {{ __('translation.Confirm') }}
            </x-modal.title>
        </x-modal.header>
        <x-modal.body>
            <div class="mb-5">
                <p class="text-center">
                    {{ __('translation.Are you sure to cancel this order') . '?' }}
                </p>
            </div>
            <div class="text-center pb-5">
                <x-button type="button" color="gray" id="__btnCloseModalCancelCreate">
                    {{ __('translation.No, Close') }}
                </x-button>
                <x-button-link href="{{ route('custom-order.index') }}" color="red">
                    {{ __('translation.Yes, Continue') }}
                </x-button-link>
            </div>
        </x-modal.body>
    </x-modal.modal-small>


    @push('bottom_js')
        <script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>

        <script>
            const CSRF_TOKEN = '{{ csrf_token() }}';

            const customOrderTableUrl = '{{ route('custom-order.index') }}';
            const getCustomerInfoUrl = '{{ route('customer-phone.show') }}';
            const selectTwoShopUrl = '{{ route('shop.select') }}';

            const enterKeyCode = 13;

            var productId = 0;

            var subTotal = 0;
            var shippingCost = 0;
            var grandTotal = 0;


            const textCreateOrder = '{{ __('translation.Create Order') }}';
            const textProcessing = '{{ __('translation.Processing') }}';
            const textCustomerFound = '{{ __('translation.Customer found') }}';
            const textCustomerNotFound = '{{ __('translation.Customer not found. Create new customer.') }}';


            $('#shop_id').select2({
                width: 'resolve',
                ajax: {
                    type: 'GET',
                    url: selectTwoShopUrl,
                    data: function(params) {
                        return {
                            search: params.term,
                            page: params.page || 1
                        };
                    },
                    delay: 500
                }
            });


            $('.channel-item').on('click', function() {
                let selectedName = $(this).data('name');

                $('.channel-item').each(function() {
                    $(this).removeClass('border-yellow-500')
                        .addClass('border-gray-300');
                });

                $(this).removeClass('border-gray-300')
                    .addClass('border-yellow-500');

                $('#__selectedChannelOutput').html(selectedName);
            });


            $(window).on('load', function() {
                $('input[type="text"]').val('');
                $('input[type="number"]').val('');
                // $('input[type="radio"]').prop('checked', false);
                $('select').val('');
                $('#shop_id').val('').trigger('change');
            });


            $(document).ready(function() {
                const fetchCustomerData = phoneNumber => {
                    $.ajax({
                        type: 'GET',
                        url: getCustomerInfoUrl,
                        data: {
                            phoneNumber: phoneNumber
                        },
                        success: function(responseJson) {
                            let customerData = responseJson.data;

                            $('#customer_name').val(customerData.customer_name);
                            $('#contact_phone').val(customerData.contact_phone);

                            $('#__fetchCustomerResultMessage').removeClass('hidden');
                            $('#__fetchCustomerResultMessage').find('span')
                                                            .addClass('text-green-500')
                                                            .removeClass('text-yellow-500')
                                                            .html(`${textCustomerFound} : ${customerData.contact_phone}`);

                        },
                        error: function(error) {
                            let responseJson = error.responseJSON;

                            let alertMessage = responseJson.message;

                            if (error.status == 404) {
                                $('#__fetchCustomerResultMessage').removeClass('hidden');
                                $('#__fetchCustomerResultMessage').find('span')
                                                                .addClass('text-red-500')
                                                                .removeClass('text-green-500')
                                                                .html(textCustomerNotFound);

                                $('#__customerNameWrapper').removeClass('hidden');
                                $('#__contactPhoneWrapper').removeClass('hidden');
                            }

                            if (error.status != 404) {
                                alert(alertMessage);
                            }

                            throw error;
                        }
                    });
                }


                $('#search_contact_phone').on('keyup', function(event) {
                    let keyCode = event.keyCode || event.which;

                    if (keyCode != enterKeyCode) {
                        $('#__fetchCustomerResultMessage').addClass('hidden');
                        $('#__fetchCustomerResultMessage').find('span')
                                                    .removeClass('text-red-500 text-gree-500')
                                                    .html(null);

                        $('#__customerNameWrapper').addClass('hidden');
                        $('#__contactPhoneWrapper').addClass('hidden');

                        $('#customer_name').val('');
                        $('#contact_phone').val('');

                    }
                });


                $('#search_contact_phone').on('keypress', function(event) {
                    let keyCode = event.keyCode || event.which;

                    if (keyCode == enterKeyCode) {
                        let contactPhone = $(this).val();
                        fetchCustomerData(contactPhone);

                        return false;
                    }
                });


                $('#__btnContactPhone').on('click', function() {
                    let contactPhone = $('#search_contact_phone').val();
                    fetchCustomerData(contactPhone);
                });


                /**
                 * ----------------------
                 *      Product Data    *
                 * ----------------------
                 */
                productId++;

                const addNewProductItem = (productId) => {
                    let productItemTemplateWrapper = $('#__productItemTemplateWrapper').clone();

                    productItemTemplateWrapper.html(function(index, html) {
                        return html.replaceAll('{productId}', productId);
                    });

                    productItemTemplate = $(productItemTemplateWrapper.html());
                    productItemTemplate.removeClass('hidden');

                    $('#__productListWrapper').append(productItemTemplate);
                }

                addNewProductItem(productId);


                $('#__btnAddProductItem').on('click', function() {
                    productId++;
                    addNewProductItem(productId);
                });


                $('body').on('click', '.product_item__remove_button', function() {
                    let productId = $(this).data('id');
                    $('#product_item_wrapper__' + productId).remove();
                });


                const sumSubTotalProduct = () => {
                    subTotal = 0;

                    $('.product_price').each(function() {
                        let productId = $(this).data('id');
                        if (productId == '{productId}') {
                            return; // continue
                        }

                        let $productPriceElement = $('#product_price__' + productId);
                        let productPrice = parseFloat($productPriceElement.val());
                        if (isNaN(productPrice)) {
                            productPrice = 0;
                        }

                        let $productQuantityElement = $('#product_quantity__' + productId);
                        let productQuantity = parseInt($productQuantityElement.val());
                        if (isNaN(productQuantity)) {
                            productQuantity = 0;
                        }

                        subTotal += (productPrice * productQuantity);
                        grandTotal = subTotal + shippingCost;

                        $('#__subTotalCurrency').html(subTotal.toLocaleString('en'));
                        $('#__grandTotalCurrency').html(grandTotal.toLocaleString('en'));
                    });
                }


                $('body').on('keyup change scroll', '.product_price', function() {
                    sumSubTotalProduct();
                });


                $('body').on('keyup change scroll', '.product_quantity', function() {
                    sumSubTotalProduct();
                });


                $('body').on('keyup change scroll', '#shipping_cost', function() {
                    shippingCost = parseFloat($(this).val());
                    if (isNaN(shippingCost)) {
                        shippingCost = 0;
                    }

                    grandTotal = subTotal + shippingCost;

                    $('#__shippingCostCurrency').html(shippingCost.toLocaleString('en'));
                    $('#__grandTotalCurrency').html(grandTotal.toLocaleString('en'));
                });


                $('body').on('change', '.product_image__field', function(event) {
                    let selectedImage = event.target.files[0];

                    let fileReader = new FileReader();

                    let productId = $(this).data('id');

                    let $productImageWrapper = $(this).parent('.product_image__wrapper_' + productId);
                    let $imgThumbnailElement = $productImageWrapper.find('.product_image__thumbnail');
                    let $removeButtonThumbnailElement = $productImageWrapper.find('.product_images__remove_button');

                    fileReader.onload = fileEvent => {
                        let imageUrl = fileEvent.target.result;

                        $imgThumbnailElement.removeClass('hidden');
                        $imgThumbnailElement.attr('src', imageUrl);

                        $removeButtonThumbnailElement.removeClass('hidden');
                        $removeButtonThumbnailElement.addClass('block');
                    }

                    fileReader.readAsDataURL(selectedImage);
                });


                $('body').on('click', '.product_images__remove_button', function() {
                    let productId = $(this).data('id');

                    let $productImageWrapper = $(this).closest('.product_image__wrapper_' + productId);
                    let $imageInputField = $productImageWrapper.find('.product_image__field');
                    let $imageThumbnail = $productImageWrapper.find('.product_image__thumbnail');

                    $(this).addClass('hidden').removeClass('block');

                    $imageThumbnail.addClass('hidden');
                    $imageThumbnail.attr('src', '#');

                    $imageInputField.val(null);
                });
            });


            $('#__formCreateCustomOrder').on('submit', function(event) {
                event.preventDefault();

                let formData = new FormData($(this)[0]);

                $.ajax({
                    type: 'POST',
                    url: '{{ route('custom-order.store') }}',
                    data: formData,
                    processData: false,
                    contentType: false,
                    beforeSend: function() {
                        $('.alert').addClass('hidden');
                        $('#__alertSuccessContent').html(null);
                        $('#__alertDangerContent').html(null);

                        $('#__btnCancelCreateOrder').attr('disabled', true);
                        $('#__btnSubmitCreateOrder').attr('disabled', true).html(textProcessing);
                    },
                    success: function(responseData) {
                        $('html, body').animate({
                            scrollTop: 0
                        }, 500);

                        $('#__alertSuccessContent').html(responseData.message);
                        $('#__alertSuccess').removeClass('hidden');

                        setTimeout(() => {
                            window.location.href = customOrderTableUrl;
                        }, 2500);
                    },
                    error: function(error) {
                        let responseJson = error.responseJSON;

                        $('html, body').animate({
                            scrollTop: 0
                        }, 500);

                        $('#__btnCancelCreateOrder').attr('disabled', false);
                        $('#__btnSubmitCreateOrder').attr('disabled', false).html(textCreateOrder);

                        if (error.status == 422) {
                            let errorFields = Object.keys(responseJson.errors);
                            errorFields.map(field => {
                                $('#__alertDangerContent').append(
                                    $('<span/>', {
                                        class: 'block mb-1',
                                        html: `- ${responseJson.errors[field][0]}`
                                    })
                                );
                            });

                        } else {
                            $('#__alertDangerContent').html(responseJson.message);

                        }

                        $('#__alertDanger').removeClass('hidden');
                    }
                })

                return false;
            });


            $('#__btnCancelCreateOrder').on('click', function() {
                $('#__modalCancelCreate').removeClass('modal-hide');
                $('body').addClass('modal-open');
            });


            $('#__btnCloseModalCancelCreate').on('click', function() {
                $('#__modalCancelCreate').addClass('modal-hide');
                $('body').removeClass('modal-open');
            });
        </script>
    @endpush

</x-app-layout>
