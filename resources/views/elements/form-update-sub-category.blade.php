@csrf
<input type="hidden" name="id" id="id" value="{{ $editData->id ? $editData->id : '' }}">

<div class="form-group">
    <label for="cat_name">Sub Category Name</label>
    <input type="text" name='cat_name' class="form-control" id="cat_name" aria-describedby="emailHelp" placeholder="Sub Category Name" required value="@if(isset($editData)){{$editData->cat_name}}@else{{old('cat_name')}}@endif">
</div>
<div class="form-group">
    <label for="parent_category_id">Select Category</label>
    <select style="width: 100%;" class="form-control" name="parent_category_id">
        <option></option>

        @if (isset($editData))
            @if (isset($categories))
                @foreach ($categories as $cateroy)
                    <option value="{{$cateroy->id}}" @if($editData->parent_category_id == $cateroy->id) selected @endif>{{$cateroy->cat_name}}</option>
                @endforeach
            @endif
        @endif
    </select>
</div>
<div class="form-group">
    <label class="exampleInputEmail12">
        Upload Image
    </label>
    <input type="file" onchange="previewFile2(this);" class="block mt-1 w-full" name="sub_category_image" id="image2">
</div>
@if(!empty($editData->image))
    <img id="previewImg2" style="margin-top: 15px;" width="180" height="180" src="{{asset($editData->image)}}" alt="Placeholder">
@else
    <img id="previewImg2" style="margin-top: 15px;" width="180" height="180" src="{{asset('img/No_Image_Available.jpg')}}" alt="Placeholder">
@endif

<div class="flex justify-end py-6">
    <x-button type="reset" color="gray" class="mr-1" id="cancelModalUpdate">
        {{ __('translation.Cancel') }}
    </x-button>
    <x-button type="submit" color="blue">
        {{ __('translation.Update') }}
    </x-button>
</div>

<script>
    $(document).ready(function() {
        $('#cancelModalUpdate').click(function() {
            $('body').removeClass('modal-open');
            $('.modal-update').addClass('modal-hide');
        });
    });

    function previewFile2(input){
        var file = $("#image2").get(0).files[0];

        if(file){
            var reader = new FileReader();

            reader.onload = function(){
                $("#previewImg2").attr("src", reader.result);
            }
            reader.readAsDataURL(file);
        }
    }
</script>
