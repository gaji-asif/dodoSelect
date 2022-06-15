@csrf

@push('top_css')
    <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <script src="//code.jquery.com/jquery-1.11.1.min.js"></script>
@endpush

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

<div>
    <div class="mb-5">
        <x-label>
            {{ __('translation.Result') }}
        </x-label>
        <x-form.textarea name="defect_result" id="defect_result" class="border-radius border-gray-300 form-control" rows="3" readonly>{{ $defectProduct->defect_result ? $defectProduct->defect_result : 'No result has been entered.' }}</x-form.textarea>
    </div>
</div>

<div class="flex justify-end py-6">
    <x-button type="reset" color="gray" class="mr-1" id="btnCancelModalResult">
        {{ __('translation.Close') }}
    </x-button>
</div>

<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
<script src="https://code.jquery.com/jquery-2.1.4.min.js"></script>

<script>

    $(document).ready(function() {
        $('#closeModalResult').click(function() {
            $('body').removeClass('modal-open');
            $('.modal_result').addClass('modal-hide');
        });

        $('#btnCancelModalResult').click(function() {
            $('body').removeClass('modal-open');
            $('.modal_result').addClass('modal-hide');
        });
    });
</script>
