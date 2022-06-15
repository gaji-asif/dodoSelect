
     <x-app-layout>
      @section('title', 'track page')


     <div class="h-screen w-screen py-12 flex items-center justify-center flex-col">
        <div class="flex md:w-1/5 justify-center">
          <img class="w-6/12 md:w-4/5" src="@if(isset(Auth::user()->logo)) {{ asset(Auth::user()->logo) }} @else {{ asset('img/dodoselect.png') }} @endif " alt="">
        </div>
        <div class="grid grid-cols-12 pt-6 gap-3 px-6">
          <div class="col-span-12" id="cardForm">
            <div class="bg-white rounded-md w-full overflow-hidden shadow">
              <div class="p-6 rounded-lg bg-white">
                <form method="POST" action="{{ route('front page') }}">
                  @csrf
                  <div class="line my-4 relative">
                    @if ($errors->any())
                    <div>
                      <div class="font-medium text-red-600">
                        {{ __('translation.Oops! Ada yang salah.') }}
                      </div>

                      <ul class="mt-3 list-disc list-inside text-sm text-red-600">
                        @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                      </ul>
                    </div>
                    @endif
                    <div id="error"></div>
                    <div>
                      <x-label>Tracking Id</x-label>
                      <x-input type="text" name="shop_id" :value="old('shop_id')" required>
                      </x-input>
                    </div>
                    {{-- <div>
                      <x-label>Shop Code</x-label>
                      <x-input type="text" name="shop_id" :value="old('shop_id')" required>
                      </x-input>
                    </div>
                    <div class="mt-6">
                      <x-label>Phone</x-label>
                      <x-input type="text" name="phone" :value="old('phone')" required></x-input>
                    </div>
                    <div class="mt-6">
                      <x-label>Name</x-label>
                      <x-input type="text" name="name" :value="old('name')" required></x-input>
                    </div> --}}
                    <div class="mt-6">
                      <x-button id="track" class="w-full flex justify-center" color="blue" type="button">Submit</x-button>
                    </div>
                  </div>
                </form>
              </div>
            </div>
          </div>
          {{-- card --}}

        </div>
      </div>


  <script type="text/javascript">


    $(document).ready(function(){

      $('input[name=name]').keyup(function(){
        if ($(this).val().length >= 3) {
          getData();
        }
      });

      // function getToken(){
      //   $.get('/token', function(token){
      //     $('input[name=_token]').val(token);
      //   });
      //   window.setTimeout(getToken, 60000);
      // }

      // getToken();

      $('#track').click(function(){


        if($('input[name=shop_id]').val().length == 0 || $('input[name=phone]').val().length == 0 || $('input[name=name]').val().length == 0){

          $('#error').html(`
            <x-alert-danger>
               <ul class="mt-3 list-disc list-inside text-sm text-red-600"></ul>
            </x-alert-danger>
          `);

         if ($('input[name=shop_id]').val().length == 0) {

          $('#error ul').append(`
            <li>Please fill the columns shop id</li>
            `);
        }

        if ($('input[name=phone]').val().length == 0) {

          $('#error ul').append(`
            <li>Please fill the columns phone</li>
            `);
        }

        if ($('input[name=name]').val().length == 0) {

          $('#error ul').append(`
            <li>Please fill the columns name</li>
            `);

        }
      } else {

        getData();
      }

    });

      function getData(){

        $.ajax({
          url:$('form').attr('action')+'?_token='+$('input[name=_token]').val(),
          type:'post',
          data:$('form').serialize()
        }).done(function(result){

          if (result.status) {
            $('#cardData').removeClass('none');
            $('#error').html('');
            $('table tbody').html(result.html);
            $('#cardForm').addClass('md:col-span-4');
          } else {
            $('#cardForm').removeClass('md:col-span-4');
            $('#cardData').addClass('none');
            $('#error').html(`
              <x-alert-danger>
              <ul class="mt-3 list-disc list-inside text-sm text-red-600">
              <li>`+ result.message +`</li>
              </ul>
              </x-alert-danger>
              `);

          }

        });
      }

    });


  </script>

</x-app-layout>


