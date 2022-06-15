<x-app-layout>
    @section('title')
        {{ __('translation.Stock Adjustment') }}
    @endsection

    @push('top_css')
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/css/bootstrap.min.css">
    @endpush

    @if (in_array('Can access menu: Stock Adjust - Adjustment', session('assignedPermissions')))
        <x-card title="{{ __('translation.Product Stock Adjustment') }}">

            <x-alert-danger class="alert mb-5 hidden" id="__alertDanger">
                <span id="__content_alertDanger"></span>
            </x-alert-danger>

            <x-alert-success class="alert mb-5 hidden" id="__alertSuccess">
                <span id="__content_alertSuccess"></span>
            </x-alert-success>

            <form method="post" action="{{ route('submit input') }}" id="__form_StockAdjustment">
                @csrf

                <div class="mb-2 flex flex-row items-center gap-3">
                    <div>
                        <input type="radio" name="check" value="1" id="__add_stock_StockAdjustment" class="radio-as-button">
                        <label for="__add_stock_StockAdjustment" class="font-bold">
                            <span class="relative left-2">
                                {{ __('translation.Add Stock') }}
                            </span>
                            <i class="bi bi-check2 text-white relative -top-1 -right-2"></i>
                        </label>
                    </div>
                    <div>
                        <input type="radio" name="check" value="0" id="__remove_stock_StockAdjustment" class="radio-as-button">
                        <label for="__remove_stock_StockAdjustment" class="font-bold">
                            <span class="relative left-2">
                                {{ __('translation.Remove Stock') }}
                            </span>
                            <i class="bi bi-check2 text-white relative -top-1 -right-2"></i>
                        </label>
                    </div>
                </div>

                <div class="mb-6 flex flex-row items-center justify-between">
                    <div class="w-full sm:w-full">
                        <x-input type="text" id="__product_id_StockAdjustment" placeholder="Enter Qr Code" autocomplete="off" />
                    </div>
                </div>

                <hr class="w-full border border-dashed border-r-0 border-b-0 border-l-0 border-blue-500">

                <div class="mt-5">
                    <div id="__wrapper_ProductList"></div>
                </div>

                <div class="mt-2 hidden" id="__button_wrapper_StockAdjustment">
                    <div class="text-center">
                        <x-button type="reset" color="gray" id="__resetButton_StockAdjustment">
                            {{ __('translation.Reset') }}
                        </x-button>
                        <x-button type="submit" color="blue" id="__submitButton_StockAdjustment">
                            {{ __('translation.Update Data') }}
                        </x-button>
                    </div>
                </div>
            </form>


            <div class="hidden" id="__templateProductItem">
                <div class="flex flex-row mb-5 py-4 border border-solid border-t-0 border-r-0 border-l-0 border-gray-200" id="__row_ProductItem_{product_code}">
                    <input type="hidden" name="product_id[]" value="{product_id}">

                    <div class="w-1/4 sm:w-1/4 md:w-1/6 mb-4 md:mb-0">
                        <div class="mb-4">
                            <img src="No-Image-Found.png" alt="Image" class="w-full h-auto rounded-sm">
                        </div>
                        <div class="block lg:hidden">
                            <x-button type="button" color="red" class="block w-full" data-code="{product_code}" onClick="removeProductItem(this)">
                        <span class="block sm:hidden">
                            <i class="fas fa-times"></i>
                        </span>
                                <span class="hidden sm:block">
                            {{ __('translation.Remove') }}
                        </span>
                            </x-button>
                        </div>
                    </div>
                    <div class="w-3/4 sm:w-3/4 md:w-5/6 ml-4 sm:ml-6">
                        <div class="grid grid-cols-1 lg:grid-cols-3 xl:grid-cols-6 lg:gap-x-5 lg:pt-1">
                            <div class="mb-2 xl:mb-4 lg:col-span-2 xl:col-span-3">
                                <label class="hidden lg:block mb-0">
                                    {{ __('translation.Product Name') }} :
                                </label>
                                <p class="font-bold">
                                    {product_name} <br>
                                    <span class="text-blue-500">{product_code}</span>
                                </p>
                            </div>
                            <div class="mb-2 xl:mb-4 lg:col-span-1 xl:col-span-1">
                                <label class="mb-0">
                                    {{ __('translation.Qty') }} :
                                </label>
                                <span class="font-bold lg:block">
                            {qty}
                        </span>
                            </div>
                            <div class="mb-4 lg:mb-0 lg:mt-2 xl:mt-0 lg:col-span-2 xl:col-span-2">
                                <label class="mb-0">
                                    {{ __('translation.Adjust Stock') }} :
                                </label>
                                <div class="w-full sm:w-1/2 md:w-1/4 lg:w-1/3 xl:w-1/2">
                                    <x-input type="number" name="adjust_stock[]" value="0" min="1" max="{max_adjust_stock_qty}" class="adjust-stock__field" />
                                </div>
                            </div>
                            <div class="hidden lg:block lg:col-span-1 xl:col-span-6 text-right lg:text-left lg:mt-10 xl:mt-3">
                                <x-button type="button" color="red" class="block lg:relative w-full lg:w-auto" data-code="{product_code}" onClick="removeProductItem(this)">
                                    {{ __('translation.Remove') }}
                                </x-button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>


            <div class="modal fade" tabindex="-1" role="dialog" id="error_modal">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title"><strong>Invalid Product Code</strong></h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <p>Your Given Product Code is Wrong. please try again</p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal fade" tabindex="-1" role="dialog" id="child_product_warning_modal">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title"><strong>Warning: Child Product</strong></h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <p>This is child product. It's quantity sync is linked to parent product. Instead of this, adjust stock of <strong><span id="PARENT_PRODUCT_CODE"></span></strong></p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>


            <x-modal.modal-small class="modal-hide" id="__modalScanQrcode">
                <x-modal.header>
                    <x-modal.title>
                        {{ __('translation.Qrcode Scanner') }}
                    </x-modal.title>
                    <x-modal.close-button id="__btnCloseModalScanQrcode" />
                </x-modal.header>
                <x-modal.body>
                    <div class="pb-10">
                        <div class="mb-1 px-4 py-2 bg-gray-200 text-gray-900 text-center uppercase truncate" id="qrcode-reader__status">
                            {{ __('translation.Idle') }}
                        </div>
                        <div id="qrcode--reader__placeholder" class="w-full h-60 md:h-52 lg:h-56 xl:h-[19rem] bg-black"></div>
                        <div id="qrcode-reader" class="w-full"></div>
                        <div class="w-full pt-3 mt-2">
                            <label for="qrcode-reader__selectCamera" class="block w-full text-center mb-0">
                                {{ __('translation.Select Camera') }}
                            </label>
                            <x-select id="qrcode-reader__selectCamera" style="display: none"></x-select>
                        </div>
                    </div>
                </x-modal.body>
            </x-modal.modal-small>

        </x-card>
    @endif

    @push('bottom_js')
        <script src="https://cdn.jsdelivr.net/npm/typeahead.js@0.11.1/dist/typeahead.bundle.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/html5-qrcode@2.0.3/minified/html5-qrcode.min.js"></script>

        <script>
            const productSelectTwoUrl = '{{ route('product.select2.seller') }}';

            const ADD_STOCK = 1;
            const REMOVE_STOCK = 0;

            var selectedProductsToList = [];

            const productSource = {!! $products->toJson() !!};


            $(window).on('load', function() {
                $('#__product_id_StockAdjustment').focus();
            });


            $(document).on('keypress', '#__form_StockAdjustment', function(event) {
                let keyboardCode = event.keyCode || event.which;

                if (keyboardCode == 13) { // enter key
                    event.preventDefault();
                    return false;
                }
            });


            const disableOneOfTypeField = (selectedType) => {
                $('#__add_stock_StockAdjustment').prop('disabled', true);
                $('#__remove_stock_StockAdjustment').prop('disabled', false);

                if (selectedType == ADD_STOCK) {
                    $('#__remove_stock_StockAdjustment').prop('disabled', true);
                    $('#__add_stock_StockAdjustment').prop('disabled', false);
                }
            }


            const substringMatcher = function(strs) {
                return function findMatches(q, cb) {
                    var matches, substringRegex;
                    matches = [];

                    substrRegex = new RegExp(q, 'i');

                    $.each(strs, function(i, str) {
                        if (substrRegex.test(str)) {
                            matches.push(str);
                        }
                    });

                    cb(matches);
                };
            };


            const initializeTypeAheadField = () => {
                $('#__product_id_StockAdjustment').typeahead({
                    hint: true,
                    minLength: 1,
                    highlight: true
                }, {
                    source: substringMatcher(productSource)
                });
            }

            initializeTypeAheadField();


            $(document).on('click', '.tt-suggestion', function() {
                renderProductToList($(this).text());
            });


            const renderProductToList = typeAheadValue => {
                let reverseTypeAheadValue = typeAheadValue.split('').reverse().join('');
                let startPosForProductCode = typeAheadValue.length - (reverseTypeAheadValue.indexOf('('));
                let endPosForProductCode = typeAheadValue.indexOf(')', typeAheadValue.length - 1);
                let productCode = typeAheadValue.substring(startPosForProductCode, endPosForProductCode);

                if (startPosForProductCode == 0 && endPosForProductCode == -1) {
                    productCode = typeAheadValue;
                }

                if (productCode !== '') {
                    $.ajax({
                        type: 'GET',
                        data: {
                            product_code: productCode
                        },
                        url: '{{ route('get_qr_code_product') }}',
                        success: function(responseJson) {
                            if (responseJson.status === 1 ) {
                                $('#error_modal').modal('show');
                            }

                            if(responseJson.product.child_of != null) {
                                $('#PARENT_PRODUCT_CODE').html(responseJson.product.child_of);
                                $('#child_product_warning_modal').modal('show');
                                return;
                            }

                            if (responseJson.status === 3) {
                                let templateProductItemElement = $('#__templateProductItem').clone();
                                let product = responseJson.product;

                                let selectedTypeValue = $('input[name="check"]:checked').val();

                                if (selectedProductsToList.indexOf(product.product_code) === -1) {
                                    selectedProductsToList.push(product.product_code);

                                    templateProductItemElement.html(function(index, html) {
                                        return html.replace('No-Image-Found.png', product.image_url);
                                    });
                                    templateProductItemElement.html(function(index, html) {
                                        return html.replaceAll('{product_id}', product.id);
                                    });
                                    templateProductItemElement.html(function(index, html) {
                                        return html.replaceAll('{product_name}', product.product_name);
                                    });
                                    templateProductItemElement.html(function(index, html) {
                                        return html.replaceAll('{product_code}', product.product_code);
                                    });
                                    templateProductItemElement.html(function(index, html) {
                                        return html.replace('{qty}', product.get_quantity.quantity);
                                    });
                                    templateProductItemElement.html(function(index, html) {
                                        if ($('input[name="check"]:checked').val() == ADD_STOCK) {
                                            return html.replace('{max_adjust_stock_qty}', 999999);
                                        }

                                        if ($('input[name="check"]:checked').val() == REMOVE_STOCK) {
                                            return html.replace('{max_adjust_stock_qty}', product.get_quantity.quantity);
                                        }

                                        return html.replace('{max_adjust_stock_qty}', 999999);
                                    });




                                    $('#__wrapper_ProductList').prepend(templateProductItemElement.html());
                                }


                                if (selectedProductsToList.indexOf(product.product_code) > -1) {
                                    let stockAdjustElement = $(`#__row_ProductItem_${product.product_code} .adjust-stock__field`);
                                    let currentValue = parseInt(stockAdjustElement.val());

                                    let increasedValue = currentValue + 1;
                                    stockAdjustElement.val(increasedValue);
                                }


                                $('#__button_wrapper_StockAdjustment').removeClass('hidden');

                                $('#__product_id_StockAdjustment').typeahead('destroy');
                                $('#__product_id_StockAdjustment').val(null);
                                initializeTypeAheadField();

                                $('#__product_id_StockAdjustment').focus();

                                disableOneOfTypeField(selectedTypeValue);
                            }
                        }
                    });
                }
            }

            $(document).on('keypress', '#__product_id_StockAdjustment', function(event) {
                let keyboardCode = event.keyCode || event.which;

                if (keyboardCode == 13) { // enter key
                    event.preventDefault();

                    let typeAheadValue = $(this).val();
                    renderProductToList(typeAheadValue);

                    return false;
                }
            });


            const resetFormAdjustment = _ => {
                selectedProductsToList = [];

                $('#__product_id_StockAdjustment').typeahead('destroy');
                $('#__product_id_StockAdjustment').val(null);
                initializeTypeAheadField();

                $('#__product_id_StockAdjustment').focus();

                $('#__wrapper_ProductList').html(null);
                $('#__button_wrapper_StockAdjustment').addClass('hidden');

                $('#__add_stock_StockAdjustment').prop('disabled', false);
                $('#__remove_stock_StockAdjustment').prop('disabled', false);

                $('.alert').addClass('hidden');
            }


            const removeProductItem = el => {
                const productCode = el.getAttribute('data-code');

                selectedProductsToList.splice(selectedProductsToList.indexOf(productCode), 1);

                $(`#__row_ProductItem_${productCode}`).remove();

                if (selectedProductsToList.length === 0) {
                    resetFormAdjustment();
                }
            }


            $('#__resetButton_StockAdjustment').click(function() {
                resetFormAdjustment();
            });


            $('#__form_StockAdjustment').submit(function(event) {
                event.preventDefault();

                let formData = new FormData($(this)[0]);

                $.ajax({
                    type: $(this).attr('method'),
                    url: $(this).attr('action'),
                    data: formData,
                    processData: false,
                    contentType: false,
                    beforeSend: function() {
                        $('#__resetButton_StockAdjustment').attr('disabled', true);
                        $('#__submitButton_StockAdjustment').attr('disabled', true).html('{{ __('translation.Updating') }}');

                        $('.alert').addClass('hidden');
                    },
                    success: function(response) {
                        $('#__resetButton_StockAdjustment').attr('disabled', false);
                        $('#__submitButton_StockAdjustment').attr('disabled', false).html('{{ __('translation.Update Data') }}');

                        $('html, body').animate({
                            scrollTop: 0
                        }, 500);

                        resetFormAdjustment();
                        $('.alert').addClass('hidden');
                        $('#__alertSuccess').removeClass('hidden');
                        $('#__content_alertSuccess').html(null);
                        $('#__content_alertSuccess').html('Data updated successfully.');
                    },
                    error: function(response) {
                        let responseJson = response.responseJSON;

                        $('#__resetButton_StockAdjustment').attr('disabled', false);
                        $('#__submitButton_StockAdjustment').attr('disabled', false).html('{{ __('translation.Update Data') }}');

                        $('.alert').addClass('hidden');
                        $('#__alertDanger').removeClass('hidden');
                        $('#__content_alertDanger').html(null);

                        $('html, body').animate({
                            scrollTop: 0
                        }, 500);

                        if (response.status == 422) {
                            let errorFields = Object.keys(responseJson.errors);
                            errorFields.map(field => {
                                $('#__content_alertDanger').append(
                                    $('<p/>', {
                                        html: responseJson.errors[field][0]
                                    })
                                );
                            });
                        }
                        else {
                            $('#__content_alertDanger').html(responseJson.message);
                        }
                    }
                });

                return false;
            });


            $('#error_modal').on('hidden.bs.modal', function() {
                $('#__product_id_StockAdjustment').typeahead('destroy');
                $('#__product_id_StockAdjustment').val(null);
                initializeTypeAheadField();

                $('#__product_id_StockAdjustment').focus();
            });

            $('#child_product_warning_modal').on('hidden.bs.modal', function() {
                $('#__product_id_StockAdjustment').typeahead('destroy');
                $('#__product_id_StockAdjustment').val(null);
                initializeTypeAheadField();

                $('#__product_id_StockAdjustment').focus();
            });
        </script>

        <script>
            const scannerBeepElement = document.createElement('audio');
            scannerBeepElement.setAttribute('src', '{{ asset('sounds/scanner-beep.mp3') }}');

            var isScanned = 0;

            scannerBeepElement.addEventListener('ended', function() {
                this.play();
            }, false);

            const qrcodeScanner = new Html5Qrcode('qrcode-reader');

            const initialQrCodeScanner = cameraId => {
                $('#qrcode--reader__placeholder').show();

                $('#qrcode-reader__status').removeClass('bg-green-200 text-green-900');
                $('#qrcode-reader__status').addClass('bg-gray-200 text-gray-900');
                $('#qrcode-reader__status').html('{{ __('translation.Idle') }}');

                qrcodeScanner.start(
                    cameraId,
                    {
                        fps: 10,
                        qrbox: 250,
                        disableFlip: false
                    },
                    onScanSuccess,
                    onScanFailure)
                    .then(params => {
                        isScanned = 0;

                        $('#qrcode--reader__placeholder').hide();

                        $('#qrcode-reader__status').removeClass('bg-green-200 text-green-900');
                        $('#qrcode-reader__status').addClass('bg-gray-200 text-gray-900');
                        $('#qrcode-reader__status').html('{{ __('translation.Scanning') }}');
                    });
            }

            const onScanSuccess = qrCodeValue => {
                if (isScanned == 0) {
                    scannerBeepElement.play();

                    $('#qrcode-reader__status').removeClass('bg-gray-200 text-gray-900');
                    $('#qrcode-reader__status').addClass('bg-green-200 text-green-900');
                    $('#qrcode-reader__status').html(`{{ __('translation.Matched') }} : ${qrCodeValue}`);

                    renderProductToList(qrCodeValue);
                }

                isScanned++;

                setTimeout(() => {
                    scannerBeepElement.pause();
                    scannerBeepElement.currentTime = 0;
                }, 500);

                setTimeout(() => {
                    qrcodeScanner.stop()
                        .then(ignore => {
                            qrcodeScanner.clear();
                        });

                    $('#__modalScanQrcode').addClass('modal-hide');
                    $('body').removeClass('modal-open');
                    $('#__btnScanQrcode').focus();
                }, 1000);
            }

            const onScanFailure = error => {
                // console.warn(`Qrcode Scanner Error : ${error}`);
            }


            const openQrcodeModal = () => {
                Html5Qrcode.getCameras()
                    .then(cameras => {
                        let qrcodeCameraId = window.localStorage.getItem('qrcode-camera-id');

                        $('#qrcode-reader__selectCamera').html(null);
                        $('#qrcode-reader__selectCamera').show();

                        if (cameras && cameras.length) {
                            cameras.map(camera => {
                                $('#qrcode-reader__selectCamera').append(
                                    $('<option/>', {
                                        value: camera.id,
                                        html: camera.label
                                    })
                                );
                            });

                            if (qrcodeCameraId === undefined || qrcodeCameraId === null) {
                                qrcodeCameraId = cameras[0].id;
                                window.localStorage.setItem('qrcode-camera-id', qrcodeCameraId);
                            }

                            $('#qrcode-reader__selectCamera').val(qrcodeCameraId);

                            initialQrCodeScanner(qrcodeCameraId);

                            $('#__modalScanQrcode').removeClass('modal-hide');
                            $('body').addClass('modal-open');
                        }
                    })
                    .catch(error => {
                        alert('Oops, something went wrong');
                        throw error;
                    });
            }


            $('#qrcode-reader__selectCamera').on('change', function() {
                let selectedCameraId = $(this).val();
                window.localStorage.setItem('qrcode-camera-id', selectedCameraId);

                qrcodeScanner.stop()
                    .then(ignore => {
                        qrcodeScanner.clear();
                        initialQrCodeScanner(selectedCameraId);
                    });
            });


            $('#__btnScanQrcode').click(function() {
                openQrcodeModal();
            });

            $('#__btnScanQrcode').keypress(function(event) {
                let keyboardCode = event.keyCode || event.which;

                if (keyboardCode == 13) { // enter key
                    openQrcodeModal();
                }
            });

            $('#__btnCloseModalScanQrcode').click(function() {
                qrcodeScanner.stop()
                    .then(ignore => {
                        qrcodeScanner.clear();
                    });

                $('#__modalScanQrcode').addClass('modal-hide');
                $('body').removeClass('modal-open');
            });
        </script>
    @endpush

</x-app-layout>
