
@push('top_css')
    <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <script src="//code.jquery.com/jquery-1.11.1.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.5.1/dropzone.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.5.1/dropzone.js"></script>

@endpush

<input type="hidden" name="id" value="{{ $data->id }}">

<div>
    <div class="mb-5">
        <fieldset class="form-group mb-5">
            <a href="javascript:void(0)" onclick="$('#defect-image').click()" id="upload_btn">Upload Image</a>
            <input type="file" id="defect-image" name="defect-image[]" style="display: none;" class="form-control" multiple="multiple">
        </fieldset>
        <div class="preview-images-zone w-full mt-3">

        </div>
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
{{--<script>--}}
{{--    $(document).ready(function() {--}}
{{--        $('#closeModalUpdate').click(function() {--}}
{{--            $('body').removeClass('modal-open');--}}
{{--            $("#defect-image").val('');--}}
{{--            $('.modal_update').addClass('modal-hide');--}}
{{--        });--}}

{{--        $('#cancelModalUpdate').click(function() {--}}
{{--            $('body').removeClass('modal-open');--}}
{{--            // $("#defect-image").val('');--}}
{{--            $('.modal_update').addClass('modal-hide');--}}
{{--        });--}}
{{--    });--}}
{{--</script>--}}
{{--<script>--}}
{{--    $(document).ready(function() {--}}
{{--        document.getElementById('defect-image').addEventListener('change', readImage, false);--}}

{{--        $(document).on('click', '.image-cancel', function() {--}}
{{--            let no = $(this).data('no');--}}
{{--            $(".preview-image.preview-show-"+no).remove();--}}
{{--        });--}}
{{--    });--}}

{{--    var num = 1;--}}
{{--    function readImage() {--}}
{{--        if (window.File && window.FileList && window.FileReader) {--}}
{{--            var files = event.target.files; //FileList object--}}
{{--            var output = $(".preview-images-zone");--}}

{{--            for (let i = 0; i < files.length; i++) {--}}
{{--                var file = files[i];--}}
{{--                if (!file.type.match('image')) continue;--}}

{{--                var picReader = new FileReader();--}}

{{--                picReader.addEventListener('load', function (event) {--}}
{{--                    var picFile = event.target;--}}
{{--                    var html =  '<div class="preview-image preview-show-' + num + '">' +--}}
{{--                        '<div class="image-cancel" data-no="' + num + '">x</div>' +--}}
{{--                        '<div class="image-zone w-full h-full"><img class=" w-full h-full" id="pro-img-' + num + '" src="' + picFile.result + '"></div>' +--}}
{{--                        '<div class="tools-edit-image"><a href="javascript:void(0)" data-no="' + num + '" class="btn btn-light btn-edit-image"></a></div>' +--}}
{{--                        '</div>';--}}

{{--                    output.append(html);--}}
{{--                    num = num + 1;--}}
{{--                });--}}

{{--                picReader.readAsDataURL(file);--}}
{{--            }--}}
{{--            // $("#defect-image").val('');--}}
{{--            // console.log($("#defect-image").val(''));--}}
{{--        } else {--}}
{{--            console.log('Browser not support');--}}
{{--        }--}}
{{--    }--}}
{{--</script>--}}
