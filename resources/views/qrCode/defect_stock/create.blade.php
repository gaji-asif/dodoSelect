<x-app-layout>
    @section('title')
        {{ __('translation.Defect Stock') }}
    @endsection

    @push('top_css')
{{--        <link type="text/css" href="http://gyrocode.github.io/jquery-datatables-checkboxes/1.2.12/css/dataTables.checkboxes.css" rel="stylesheet" />--}}
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/css/bootstrap.min.css">
        <link href="http://code.jquery.com/ui/1.10.2/themes/smoothness/jquery-ui.css">
{{--        <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />--}}
            <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
        <script src="//code.jquery.com/jquery-1.11.1.min.js"></script>
{{--            <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>--}}
{{--            <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.7/js/bootstrap.js"></script>--}}
{{--            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.2/dropzone.min.css" integrity="sha512-jU/7UFiaW5UBGODEopEqnbIAHOI8fO6T99m7Tsmqs2gkdujByJfkCbbfPSN4Wlqlb9TGnsuC0YgUgWkRBK7B9A==" crossorigin="anonymous" referrerpolicy="no-referrer" />--}}
{{--            <script src="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.2/min/dropzone.min.js" integrity="sha512-VQQXLthlZQO00P+uEu4mJ4G4OAgqTtKG1hri56kQY1DtdLeIqhKUp9W/lllDDu3uN3SnUNawpW7lBda8+dSi7w==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>--}}
        @endpush

    <style>
        table.dataTable tbody td img{
            padding: 0;
        }
        /*.hide{*/
        /*    display: none;*/
        /*}*/
        .select2-container .select2-selection--single{
            height:   36px;
            border-color: rgba(209,213,219,var(--tw-border-opacity));
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered{
            line-height: 36px;
            border: 1px blue;
        }
        .loading {
            display: inline-block;
            vertical-align: middle;
            width: 16px;
            height: 16px;
            /* background-color: #F0F0F0; */
            position: absolute;
            right: 34px;
            top: 75px;
        }
        .ui-menu .ui-menu-item-wrapper{
            padding: 3px 1em 3px .4em;
        }
        .card-body {
            padding: 1rem;
        }

        /* Example #1 */
        #autocomplete.ui-autocomplete-loading ~ #loading1 {
            background-image: url(//cdn.rawgit.com/salmanarshad2000/demos/v1.0.0/jquery-ui-autocomplete/loading.gif);
        }
        /* Example #2 */
        #loading2.isloading {
            background-image: url(//cdn.rawgit.com/salmanarshad2000/demos/v1.0.0/jquery-ui-autocomplete/loading.gif);
        }
    </style>
        <style>
            .preview-images-zone {
                border: 1px solid #ddd;
                min-height: 150px;
                position: relative;
                overflow:auto;
            }
            .preview-images-zone > .preview-image {
                height: 90px;
                width: 90px;
                position: relative;
                margin-right: 5px;
                float: left;
                margin-bottom: 5px;
            }
            .preview-images-zone > .preview-image > .image-cancel {
                font-size: 14px;
                margin-right: 8px;
                margin-top: 3px;
                cursor: pointer;
                border-radius: 50%;
                color: #de0929;
                text-align: center;
            }
            .preview-image:hover > .image-zone {
                cursor: move;
                opacity: .5;
            }
            .preview-image:hover > .image-cancel {
                display: block;
            }
        </style>

        @if (in_array('Can access menu: Stock Adjust - Defect Stock', session('assignedPermissions')))

        <x-card title="{{ __('translation.Add Defect Stock') }}">
        <div class="mt-6">
            @if (session('success'))
                <x-alert-success>
                    {{ session('success') }}
                </x-alert-success>
            @endif

            @if (session('danger'))
                <x-alert-danger>
                    {{ session('danger') }}
                </x-alert-danger>
            @endif

            @if (session('error'))
                <x-alert-danger>
                    {{ session('error') }}
                </x-alert-danger>
            @endif

            @if ($errors->any())
                <x-alert-danger>
                    <ul class="mt-3 list-disc list-inside text-sm text-red-600">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </x-alert-danger>
            @endif
        </div>

        <form method="POST" action="{{route('defect_stock.store')}}" id="form-defect-stock" enctype="multipart/form-data">
            @csrf

            <div class="card-body">
                <div class="mb-6 flex flex-row items-center justify-between">
                    <div class="loading" id="loading2"></div>
                    <div class="form-grou w-full sm:w-fullp">
                        <x-input type="text" id="product_search" placeholder="Enter Qr Code" class="qr-code" aria-describedby="emailHelp" />
                    </div>
                    <div class="w-auto mx-4 lg:mx-8 sm:w-1/6 lg:w-auto text-center">
                        <span class="font-bold text-gray-500">OR</span>
                    </div>
                    <div class="w-auto sm:w-2/5 lg:w-1/4 xl:w-1/5">
                        <div class="flex items-center justify-center sm:justify-end sm:relative sm:top-1">
                            <x-button type="button" color="yellow" id="__btnScanQrcode" class="h-10 relative top-[0.10rem] sm:-top-1 lg:w-full" title="{{ __('translation.QRcode Scan') }}">
                            <span>
                                <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-upc-scan" viewBox="0 0 16 16">
                                    <path d="M1.5 1a.5.5 0 0 0-.5.5v3a.5.5 0 0 1-1 0v-3A1.5 1.5 0 0 1 1.5 0h3a.5.5 0 0 1 0 1h-3zM11 .5a.5.5 0 0 1 .5-.5h3A1.5 1.5 0 0 1 16 1.5v3a.5.5 0 0 1-1 0v-3a.5.5 0 0 0-.5-.5h-3a.5.5 0 0 1-.5-.5zM.5 11a.5.5 0 0 1 .5.5v3a.5.5 0 0 0 .5.5h3a.5.5 0 0 1 0 1h-3A1.5 1.5 0 0 1 0 14.5v-3a.5.5 0 0 1 .5-.5zm15 0a.5.5 0 0 1 .5.5v3a1.5 1.5 0 0 1-1.5 1.5h-3a.5.5 0 0 1 0-1h3a.5.5 0 0 0 .5-.5v-3a.5.5 0 0 1 .5-.5zM3 4.5a.5.5 0 0 1 1 0v7a.5.5 0 0 1-1 0v-7zm2 0a.5.5 0 0 1 1 0v7a.5.5 0 0 1-1 0v-7zm2 0a.5.5 0 0 1 1 0v7a.5.5 0 0 1-1 0v-7zm2 0a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v7a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5v-7zm3 0a.5.5 0 0 1 1 0v7a.5.5 0 0 1-1 0v-7z"/>
                                </svg>
                            </span>
                                <span class="whitespace-nowrap hidden sm:block sm:ml-2">
                                {{ __('translation.QRcode Scan') }}
                            </span>
                            </x-button>
                        </div>
                    </div>
                </div>

                <hr class="w-full border border-dashed border-r-0 border-b-0 border-l-0 border-blue-500">

                <div class="hide full-card">
                    <div class="product_body"></div>
                </div>
                <div class="mt-2 hide full-card" id="">
                    <div class="text-center">
                        <x-button type="reset" color="gray" id="reset-button">
                            {{ __('translation.Reset') }}
                        </x-button>
                        <x-button type="submit" color="blue" id="submit-button">
                            {{ __('translation.Update Data') }}
                        </x-button>
                    </div>
                </div>
            </div>
        </form>


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
{{--        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.0/jquery.validate.js"></script>--}}
{{--        <script type="text/javascript" src="http://gyrocode.github.io/jquery-datatables-checkboxes/1.2.12/js/dataTables.checkboxes.min.js"></script>--}}
{{--            <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>--}}
{{--            <script src="https://cdn.jsdelivr.net/npm/typeahead.js@0.11.1/dist/typeahead.bundle.min.js"></script>--}}
            <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
            <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.min.js"></script>
            <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
            <script src="https://cdn.jsdelivr.net/npm/html5-qrcode@2.0.3/minified/html5-qrcode.min.js"></script>

        <script>
            $(document).ready(function() {
                $('body').on('click','.ui-menu-item-wrapper',function(){
                    $('.qr-code').keyup();
                });
                // console.log('jquery is working');
                $('.qr-code').keyup(function(event) {
                    // console.log(event);
                    var currentUrl = window.location.origin;
                    event.preventDefault();
                    if (event.keyCode !== 13) {
                        if($(this).val() !== "" )
                        {
                            $.ajax({
                                type: 'GET',
                                data: {product_code:$(this).val(),from:2},
                                // async:false,

                                url: '{{route('get-qr-code-for-defect-stocks')}}',
                            }).done(function(data) {
                                // console.log(data);
                                if(data !== '')
                                {
                                    $('.qr-code').val('');

                                    let table = $('.full-card');
                                    if($(table).hasClass('hide'))
                                    {
                                        $(table).removeClass('hide');
                                        $(table).addClass('show');
                                    }

                                    let tableBody = $('.product_body');
                                    let product_name = data.product_name;
                                    if(data.product_name === null )
                                    {
                                        product_name = '';
                                    }

                                    // let product_image = currentUrl+'/No-Image-Found.png';

                                    let product_image = data.image;
                                    if(data.image === null )
                                    {
                                        product_image = currentUrl+'/public/No-Image-Found.png';
                                    }
                                    else{
                                        product_image = currentUrl+'/public/'+product_image
                                    }

                                    $data = `
                                <div class="new" id='${data.product_code}'>
                                    <div id="templateProductItem">
                                        <div class="w-full flex flex-row mb-5 py-4 border border-solid border-t-0 border-r-0 border-l-0 border-gray-200" id="__row_ProductItem_{product_code}">
                                            <input type="hidden" id="product_id" class="product_id" name="product_id[]" value="${data.id}">
                                            <div class="w-1/4 sm:w-1/4 md:w-1/6 mb-4 md:mb-0">
                                                <div class="mb-4" style="border: 1px solid dimgray;">
                                                    <img src="${product_image}" alt="Image" class="w-full h-auto rounded-sm">
                                                </div>
                                                <div class="block {{--lg:hidden--}}">
                                                    <x-button type="button" color="red" class="block w-full removeProductItem" data-product_code="${data.product_code}">
                                                        <span class="block sm:hidden"><i class="fas fa-times"></i></span>
                                                        <span class="hidden sm:block">{{ __('translation.Remove') }}</span>
                                                    </x-button>
                                                </div>
                                            </div>
                                            <div class="w-3/4 sm:w-3/4 md:w-5/6 ml-4 sm:ml-6">
                                                <div class="grid grid-cols-1 lg:grid-cols-3 xl:grid-cols-6 lg:gap-x-5 lg:pt-1">
                                                    <div class="mb-2 xl:mb-4 lg:col-span-2 xl:col-span-2">
                                                        <p class="font-bold lg:mb-1">
                                                            ${product_name} <br>
                                                            <span class="text-blue-500">${data.product_code}</span>
                                                        </p>
                                                        <div class="grid grid-cols-1 lg:grid-cols-3 xl:grid-cols-6 lg:gap-x-5 lg:pt-1 lg:mt-3">
                                                            <div class="lg:col-span-3 xl:col-span-3">
                                                                <label class="mb-0">
                                                                    {{ __('translation.Quantity') }} :
                                                                </label>
                                                                <span class="font-bold lg:block">
                                                                    <x-input type="number" name="quantity[]" value="1" min="1"/>
                                                                </span>
                                                            </div>
                                                            <div class="lg:col-span-3 xl:col-span-3">
                                                                <label class="mb-0">
                                                                    {{ __('translation.Status') }} :
                                                                </label>
                                                                <span class="font-bold lg:block">
                                                                    <x-select name="status[]" class="text-sm">
                                                                        <option value="open">Open</option>
                                                                        <option value="close">Close</option>
                                                                    </x-select>
                                                                </span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="mb-4 lg:mb-0 xl:mt-0 lg:col-span-2 xl:col-span-2 md:col-span-3 lg:mt-2">
                                                        <label class="mb-0">
                                                            {{ __('translation.Problem') }} :
                                                        </label>
                                                        <div class="w-full ">
                                                            <x-form.textarea name="note[]" class="border-radius border-gray-300 form-control" rows="4"></x-form.textarea>
                                                        </div>
                                                    </div>
                                                    <div class="mb-4 lg:mb-0 lg:mt-2 xl:mt-0 lg:col-span-2 xl:col-span-2 md:col-span-3">
                                                        <label class="mb-0">
                                                            {{ __('translation.Result') }} :
                                                        </label>
                                                        <div class="w-full ">
                                                            <x-form.textarea name="result[]" class="border-radius border-gray-300 form-control" rows="4"></x-form.textarea>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="lg:col-span-1 xl:col-span-6 text-left lg:mt-5 xl:mt-3">
                                                    <x-button type="button" color="green" class="block lg:relative lg:w-auto show_images" data-product_code="${data.product_code}" data-id="${data.id}">
                                                        {{ __('translation.Add Images') }}
                                                    </x-button>
                                                    <input type="file" id="defect-image_${data.id}" name="defect-image[]" style="display: none;" class="form-control defect-image" multiple="multiple" readonly accept="image/*">
                                                    <div class="preview-images-zone w-full mt-3 hide" id="preview-images-zone-${data.id}">

                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>`

                                tableBody.append($data);
                                // console.log(data.id);
                            }
                        })
                    }
                }
            });

            $("body").on("click",".removeProductItem",function(){
                let product_code = $(this).data('product_code');
                $(this).parents(".new").remove();

                let item = $('.removeProductItem');

                if(item.length <= 0)
                {
                    let table = $('.full-card');
                    if($(table).hasClass('show'))
                    {
                        $(table).removeClass('show');
                        $(table).addClass('hide');
                    }
                }
                $.ajax({
                    type: 'GET',
                    data:{product_code:product_code},
                    url: '{{route('delete_session_defect_product')}}',
                    }).done(function(data) {

                    })
                });
            });

            $(document).ready(function() {
            $("body").on('click','#reset-button',function () {
                $(".product_body").html('');  $('.qr-code').val('');
                let table = $('.full-card');
                if($(table).hasClass('show'))
                {
                    $(table).removeClass('show');
                    $(table).addClass('hide');
                }

                $.ajax({
                    type: 'GET',
                    url: '{{route('reset_session_defect_product')}}',
                }).done(function(data) {})
            });
        });
        </script>

            <script type="text/javascript">

                // CSRF Token
                var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
                $(document).ready(function(){

                    $( "#product_search" ).autocomplete({
                        search: function () {
                            $("#loading2").addClass("isloading");
                        },
                        source: function( request, response ) {
                            // console.log(1);
                            // Fetch data
                            $('.spinner').show();
                            $.ajax({
                                url:"{{route('defect_stock.autocomplete')}}",
                                type: 'post',
                                dataType: "json",
                                data: {
                                    _token: CSRF_TOKEN,
                                    search: request.term
                                },
                                success: function( data ) {
                                    $('.spinner').hide();
                                    response( data );
                                }
                            });
                        },

                        response: function () {
                            $("#loading2").removeClass("isloading");
                        },
                        select: function (event, ui) {
                            $('.qr-code').val(ui.item.label);
                            $('.qr-code').val(ui.item.value);
                            return false;
                        }
                    });

                });
            </script>

            <script>
                $(document).ready(function() {
                    var num = 1;
                    $("body").on('click','.show_images',function () {
                        var product_id = $(this).data('id');
                        // console.log(product_id);
                        document.getElementById('defect-image_'+product_id).click();
                        document.getElementById('defect-image_'+product_id).addEventListener('change', readImage, false);

                        $(document).on('click', '.image-cancel', function() {
                            let no = $(this).data('no');
                            let id = $(this).data('id');
                            $("#preview-show-"+id+no).remove();
                        });

                        function readImage() {
                            // console.log(product_id);
                            if (window.File && window.FileList && window.FileReader) {
                                var files = event.target.files; //FileList object
                                var output = $("#preview-images-zone-"+product_id);

                                if($(output).hasClass('hide'))
                                {
                                    $(output).removeClass('hide');
                                    $(output).addClass('show');
                                }

                                for (let i = 0; i < files.length; i++) {
                                    var file = files[i];
                                    if (!file.type.match('image')) continue;

                                    var picReader = new FileReader();

                                    picReader.addEventListener('load', function (event) {
                                        var picFile = event.target;
                                        var html =  '<div class="preview-image preview-show-' + num + '" id="preview-show-' + product_id + num + '">' +
                                            '<div class="image-zone w-full h-full"><img class=" w-full h-full" id="pro-img-' + num + '" src="' + picFile.result + '"></div>' +
                                            '<div class="image-cancel" data-no="' + num + '" data-id="' + product_id +'">Delete</div>' +
                                            '</div>';

                                        output.append(html);
                                        num = num + 1;
                                    });

                                    picReader.readAsDataURL(file);
                                }
                                // $("#defect-image_"+product_id).val('');
                            } else {
                                console.log('Browser not support');
                            }
                        }
                    });
                });

            </script>

            {{--<script>
                $(document).ready(function() {
                    $("body").on('click','.show_images',function () {
                        var product_id = $(this).data('id');
                        console.log(product_id);
                        var el = document.getElementById('defect-image' + product_id);
                        if (el) {

                        el.addEventListener('change', readImage, false);
                        };
                        $(document).on('click', '.image-cancel', function() {
                        let no = $(this).data('no');
                        $(".preview-image.preview-show-"+no).remove();
                    });

                var num = 1;
                function readImage() {

                    --}}{{--let image_upload = new FormData();--}}{{--
                    --}}{{--let TotalImages = $('#defect-image'.product_id)[0].files.length;  //Total Images--}}{{--
                    --}}{{--let images = $('#defect-image'.product_id)[0];--}}{{--

                    --}}{{--for (let i = 0; i < TotalImages; i++) {--}}{{--
                    --}}{{--    image_upload.append('images' + i, images.files[i]);--}}{{--
                    --}}{{--}--}}{{--
                    --}}{{--image_upload.append('TotalImages', TotalImages);--}}{{--
                    --}}{{--$.ajaxSetup({--}}{{--
                    --}}{{--    headers: {--}}{{--
                    --}}{{--        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')--}}{{--
                    --}}{{--    }--}}{{--
                    --}}{{--});--}}{{--

                    --}}{{--console.log(image_upload);--}}{{--
                    --}}{{--$.ajax({--}}{{--
                    --}}{{--    url: '{{ route('dropzone.upload') }}',--}}{{--
                    --}}{{--    type: 'POST',--}}{{--
                    --}}{{--    contentType:false,--}}{{--
                    --}}{{--    processData: false,--}}{{--
                    --}}{{--    data:image_upload,--}}{{--
                    --}}{{--    success: function(result) {--}}{{--
                    --}}{{--        //Preiview Image Names--}}{{--
                    --}}{{--        if(result.type == "previewimage")--}}{{--
                    --}}{{--        {--}}{{--
                    --}}{{--            load_images();--}}{{--
                    --}}{{--        }--}}{{--
                    --}}{{--    }--}}{{--
                    --}}{{--});--}}{{--

                    --}}{{--load_images();--}}{{--

                    --}}{{--function load_images()--}}{{--
                    --}}{{--{--}}{{--
                    --}}{{--    $.ajax({--}}{{--
                    --}}{{--        url:"{{ route('dropzone.fetch') }}",--}}{{--
                    --}}{{--        success:function(data)--}}{{--
                    --}}{{--        {--}}{{--
                    --}}{{--            $('#preview-images-zone-'.product_id).html(data);--}}{{--
                    --}}{{--        }--}}{{--
                    --}}{{--    })--}}{{--
                    --}}{{--}--}}{{--
                    if (window.File && window.FileList && window.FileReader) {
                        var files = event.target.files; //FileList object
                        var output = $("#preview-images-zone-"+product_id);

                        if($(output).hasClass('hide'))
                        {
                            $(output).removeClass('hide');
                            $(output).addClass('show');
                        }

                        for (let i = 0; i < files.length; i++) {
                            var file = files[i];
                            if (!file.type.match('image')) continue;

                            var picReader = new FileReader();

                            picReader.addEventListener('load', function (event) {
                                var picFile = event.target;
                                var html =  '<div class="preview-image preview-show-' + num + '" id="preview-show-' + product_id + num + '">' +
                                    '<div class="image-cancel" data-no="' + num + '" data-id="' + product_id +'">x</div>' +
                                    '<div class="image-zone w-full h-full"><img class=" w-full h-full" id="pro-img-' + num + '" src="' + picFile.result + '"></div>' +
                                    '</div>';

                                output.append(html);
                                num = num + 1;
                            });

                            picReader.readAsDataURL(file);
                        }
                        // $("#defect-image").val('');
                        // console.log($("#defect-image").val(''));
                    } else {
                        console.log('Browser not support');
                    }
                }
                });
                });

            </script>--}}


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
