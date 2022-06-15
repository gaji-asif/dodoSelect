@csrf

@push('top_css')
    <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <script src="//code.jquery.com/jquery-1.11.1.min.js"></script>
@endpush
<style>
    .preview-images-zone {
        border: 1px solid #ddd;
        min-height: 150px;
        position: relative;
        overflow:auto;
    }
    .preview-images-zone > .preview-image {
        height: 100px;
        width: 100px;
        position: relative;
        margin-right: 5px;
        float: left;
        margin-bottom: 5px;
    }
    #product_name{
        font-size: 1.1rem;
        font-weight: bold;
    }
    #product_code{
        font-size: 1rem;
        font-weight: bold;
    }

    .inner-img {
        transition: 0.3s;
    }

    .inner-img:hover {
        transform: scale(1.5);
    }

    .myImg {
        border-radius: 5px;
        cursor: pointer;
        transition: 0.3s;
    }

    .myImg:hover {opacity: 0.7;}

    /* The Modal (background) */
    .modal {
        display: none;
        position: fixed;
        z-index: 1;
        /*padding-top: 100px; !* Location of the box *!*/
        /*left: 0;*/
        top: 0;
        /*right: 0;*/
        /*bottom: 0;*/
        width: 100%; /* Full width */
        height: 100%; /* Full height */
        overflow: auto; /* Enable scroll if needed */
        background-color: rgb(21, 14, 14); /* Fallback color */
        background-color: rgba(0, 0, 0, 0.66); /* Black w/ opacity */
    }

    /* Modal Content (image) */
    .modal-content {
        margin: auto;
        display: block;
        /*left: 20%;*/
        top: 20%;
        width: auto;
        max-width: 600px;
        max-height: 500px;
    }

    @-webkit-keyframes zoom {
        from {-webkit-transform:scale(0)}
        to {-webkit-transform:scale(1)}
    }

    @keyframes zoom {
        from {transform:scale(0)}
        to {transform:scale(1)}
    }

    /* The Close Button */
    .close {
        position: absolute;
        top: 10%;
        right: 10%;
        z-index: 1;
        color: #dee2e6;
        font-size: 45px;
        font-weight: bold;
        transition: 0.3s;
        opacity: 0.8;
    }

    .close:hover,
    .close:focus {
        color: #1c1818;
        text-decoration: none;
        cursor: pointer;
    }

    /* 100% Image Width on Smaller Screens */
    @media only screen and (min-device-width : 218px) and (max-device-width : 768px){
        .modal-content {
            width: 90%;
            /*max-width: 300px;*/
            /*max-height: 320px;*/
            /*left: 10%;*/
            /*margin-top: 50%;*/
        }
    }

</style>

<div class="grid mb-2">
    <div class="mt-4 mb-5">
        <div class="names" id="product_name">
            {{ $defectProduct->product->product_name ? $defectProduct->product->product_name : '' }}
        </div>
    </div>

    <div class="mb-5">
        <div class="text-blue-600 codes" id="product_code">
            {{ $defectProduct->product->product_code ? $defectProduct->product->product_code : '' }}
        </div>
    </div>
</div>

{{--<div>--}}
{{--    <div class="mb-5">--}}
{{--        <x-label>--}}
{{--            {{ __('translation.Result') }}--}}
{{--        </x-label>--}}
{{--        <x-form.textarea name="defect_result" disabled id="defect_result" class="border-radius border-gray-300 form-control" rows="3">{{ $defectProduct->defect_result ? $defectProduct->defect_result : '' }}</x-form.textarea>--}}
{{--    </div>--}}
{{--</div>--}}

<div>
    <div class="mb-5">
        <div class="preview-images-zone w-full mt-3 pt-3">
            @php $i = 1; @endphp
            @if(isset($defectImages) && count($defectImages) > 0)
                @foreach($defectImages as $key=>$defectImage)
                    @if (!empty($defectImage->image) && file_exists(public_path($defectImage->image)))
                        <div class="preview-image preview-show-{{$i}} zoom" data-id="{{$i}}">
                            <div class="image-zone w-full h-full"><img class="w-full h-full img" id="pro-img-{{$i}}" data-id="{{$i}}" src="{{asset($defectImage->image)}}"></div>
                        </div>
                        @php $i = $i + 1; @endphp
                    @endif
                @endforeach
            @else
                <p>There are no images uploaded.</p>
            @endif
        </div>
        <div id="myModal" class="modal">
            <span class="close">&times;</span>
            <img class="modal-content" id="img01">
        </div>
    </div>
</div>

<div class="flex justify-end py-6">
    <x-button type="reset" color="gray" class="mr-1" id="btnCancelModalImage">
        {{ __('translation.Close') }}
    </x-button>
</div>

<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
<script src="https://code.jquery.com/jquery-2.1.4.min.js"></script>


<script>
    // Get the modal
    var modal = document.getElementById("myModal");

    var modalImg = document.getElementById("img01");
    $('.img').click(function(){
        var id=$(this).attr('id');
        var img = document.getElementById(id);
        img.classList.add('myImg');
        // img.onclick = function(){
        modal.style.display = "block";
        modalImg.src = this.src;
    });

    // Get the <span> element that closes the modal
    var span = document.getElementsByClassName("close")[0];

    // When the user clicks on <span> (x), close the modal
    span.onclick = function() {
        modal.style.display = "none";
    }
</script>


<script>

    $(document).ready(function() {
        $('#closeModalImage').click(function() {
            $('body').removeClass('modal-open');
            $('.modal_image').addClass('modal-hide');
        });

        $('#btnCancelModalImage').click(function() {
            $('body').removeClass('modal-open');
            $('.modal_image').addClass('modal-hide');
        });

        $('img').on('click', function() {
            $('#overlay')
                .css({backgroundImage: `url(${this.src})`})
                .addClass('open')
                .one('click', function() { $(this).removeClass('open'); });
        });

        // var previous=0;
        //
        // $('.zoom').click(function(){
        //     var s=$(this).attr('id');
        //     $('#'+s).animate({'width':'200px'});
        //     $('#'+s).css({'cursor':'zoom-out'});
        //     if($('#'+previous).width()!=100)
        //     {
        //         $('#'+previous).animate({'width':'100px'});
        //         $('#'+previous).css({'cursor':'zoom-in'});
        //     }
        //     previous=s;
        // });
    });
</script>
