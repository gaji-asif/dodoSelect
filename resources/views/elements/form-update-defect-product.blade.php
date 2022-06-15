@csrf

@push('top_css')
    <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <script src="//code.jquery.com/jquery-1.11.1.min.js"></script>
@endpush
<style>
    .preview-images-zone {
        border: 1px solid #ddd;
        min-height: 100px;
        position: relative;
        overflow:auto;
    }
    .preview-images-zone > .preview-image {
        width: 90px;
        height: 90px;
        position: relative;
        float: left;
    }
    .preview-images-zone > .preview-image > .image-cancel {
        font-size: 14px;
        cursor: pointer;
        color: #de0929;
        text-align: center;
    }
    .preview-image:hover > .image-zone {
        cursor: move;
        opacity: .5;
    }
    #upload_btn{
        border: 1px solid #6969c4;
        background-color: #ccccec;
        padding: 4px 5px;
        border-radius: 6px;
    }
</style>

<input type="hidden" name="id" value="{{ $data->id }}">

<div class="grid md:grid-cols-2 md:gap-x-5 mb-2">
    <div class="mb-5">
        <x-label>
            {{ __('translation.Select Status') }}
        </x-label>
        <x-select name="status" id="status">
            <option value="open" {{$data->deffect_status == 'open' ? 'selected' : ''}}>{{ __('translation.Open') }}</option>
            <option value="close" {{$data->deffect_status == 'close' ? 'selected' : ''}}>{{ __('translation.Close') }}</option>
        </x-select>
    </div>

    <div class="mb-5">
        <x-label>
            {{ __('translation.Quantity') }}
        </x-label>
        <x-input type="number" name="quantity" id="quantity" min="1" value="{{ $data->quantity ? $data->quantity : old('quantity') }}" required />
    </div>
</div>

<div>
    <div class="mb-5">
        <x-label>
            {{ __('translation.Problem') }}
        </x-label>
        <x-form.textarea name="deffect_note" id="deffect_note" class="border-radius border-gray-300 form-control" rows="3">{{ $data->deffect_note ? $data->deffect_note : old('deffect_note') }}</x-form.textarea>
    </div>
</div>

<div>
    <div class="mb-5">
        <x-label>
            {{ __('translation.Result') }}
        </x-label>
        <x-form.textarea name="defect_result" id="defect_result" class="border-radius border-gray-300 form-control" rows="3">{{ $data->defect_result ? $data->defect_result : old('defect_result') }}</x-form.textarea>
    </div>
</div>

<div >
    <div class="mb-5">
        <fieldset class="form-group mb-5">
            <a href="javascript:void(0)" onclick="$('#defect-image').click()" id="upload_btn">Upload Image</a>
            <input type="file" id="defect-image" name="defect-image[]" style="display: none;" class="form-control" multiple="multiple">
        </fieldset>
        <div class="preview-images-zone w-full mt-3">
            @php $i = 1; @endphp
            @if(isset($defectImages) && count($defectImages) > 0)
                @foreach($defectImages as $key=>$defectImage)
                    @if (!empty($defectImage->image) && file_exists(public_path($defectImage->image)))
                        <div class="preview-image preview-show-{{$i}} mb-6 mr-1">
                            <div class="image-zone w-full h-full"><img class="w-full h-full" id="pro-img-{{$i}}" src="{{asset($defectImage->image)}}"></div>
{{--                            <div class="image-cancel" data-no="{{$i}}">Delete</div>--}}
                        </div>
                        @php $i = $i + 1; @endphp
                    @endif
                @endforeach
            @endif
        </div>
        <input type="number" hidden value="{{$i}}" id="image_number">
    </div>
</div>

<div class="flex justify-end py-6">
    <x-button type="reset" color="gray" class="mr-1" id="cancelModalUpdate">
        {{ __('translation.Cancel') }}
    </x-button>
    <x-button type="submit" color="blue">
        {{ __('translation.Update') }}
    </x-button>
</div>

<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>

<script>
    $(document).ready(function() {
        $('#closeModalUpdate').click(function() {
            $('body').removeClass('modal-open');
            $("#defect-image").val('');
            $('.modal_update').addClass('modal-hide');
        });

        $('#cancelModalUpdate').click(function() {
            $('body').removeClass('modal-open');
            // $("#defect-image").val('');
            $('.modal_update').addClass('modal-hide');
        });
    });
</script>
<script>
    $(document).ready(function() {
        document.getElementById('defect-image').addEventListener('change', readImage, false);

        $(document).on('click', '.image-cancel', function() {
            let no = $(this).data('no');
            $(".preview-image.preview-show-"+no).remove();
        });
    });

    var num = parseInt(document.getElementById('image_number').value);
    function readImage() {
        if (window.File && window.FileList && window.FileReader) {
            var files = event.target.files; //FileList object
            var output = $(".preview-images-zone");

            for (let i = 0; i < files.length; i++) {
                var file = files[i];
                if (!file.type.match('image')) continue;

                var picReader = new FileReader();

                picReader.addEventListener('load', function (event) {
                    var picFile = event.target;
                    var html =  '<div class="preview-image preview-show-' + num + ' mb-6 mr-1">' +
                        '<div class="image-zone w-full h-full"><img class=" w-full h-full" id="pro-img-' + num + '" src="' + picFile.result + '"></div>' +
                        '<div class="image-cancel mt-1" data-no="' + num + '">Delete</div>' +
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
        $('#image_number').val(num)
    }
</script>
