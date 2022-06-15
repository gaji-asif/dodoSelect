@csrf

<input type="hidden" name="id" value="{{ $data->id }}">

<div class="mt-4">
    <label class="block font-medium text-sm text-gray-700">
        New Password
    </label>
    <x-input type="password" name="new-password"></x-input>
</div>

<div class="mt-4">
    <label class="block font-medium text-sm text-gray-700">
        Confirm New Password
    </label>
    <x-input type="password" name="new-password_confirmation"></x-input>
</div>

<div class="flex justify-end py-6">
    <x-button type="reset" color="gray" class="mr-1" id="cancelModalPassword">
        {{ __('translation.Cancel') }}
    </x-button>
    <x-button type="submit" color="blue">
        {{ __('translation.Change Password') }}
    </x-button>
</div>
<script>
    $(document).ready(function() {
        $('#closeModalPassword').click(function() {
            $('body').removeClass('modal-open');
            $('.modal-password').addClass('modal-hide');
        });

        $('#cancelModalPassword').click(function() {
            $('body').removeClass('modal-open');
            $('.modal-password').addClass('modal-hide');
        });
    });
</script>
