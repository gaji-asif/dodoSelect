<x-app-layout>
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>

    @section('title', 'Profile')

    <div class="col-span-12">

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            <div>
                <x-card.card-default>
                    <x-card.header>
                        <x-card.title>
                            {{ __('translation.Profile') }}
                        </x-card.title>
                    </x-card.header>
                    <x-card.body>
                        <form method="POST" class="mx-2" action={{url('/profile-update')}} enctype="multipart/form-data">
                            @csrf
                            @if ($errors->any())
                                <div class="mb-4">
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
                            @if (session()->has('success'))
                                <div class="mb-4 text-md font-medium text-green-600">
                                    {{ session()->get('success') }}
                                </div>
                            @endif

                            @if (session()->has('error'))
                                <div class="mb-4 text-md font-medium text-red-600">
                                    {{ session()->get('error') }}
                                </div>
                            @endif
                            <div class="mt-4">
                                <label class="block font-medium text-sm text-gray-700">
                                    Shop Name
                                </label>
                                <input id="name" class="block mt-1 w-full form-control" type="text" name="name" value="{{ $user->name }}" required {{ $user->role == 'dropshipper' ? 'disabled' : ''}}>
                            </div>
                            @if($user->role == 'dropshipper')
                                <div class="mt-4">
                                    <label class="block font-medium text-sm text-gray-700">
                                        Business Address
                                    </label>
                                    <textarea name="address" class="block mt-1 w-full form-control" disabled rows="3">{{$dropshipperAddress->address ?? ''}}&#13;&#10;{{$dropshipperAddress->sub_district ?? ''}}, {{$dropshipperAddress->district ?? ''}}&#13;&#10;{{$dropshipperAddress->province ?? ''}}, {{$dropshipperAddress->postcode ?? ''}}</textarea>
                                </div>
                            @endif
                            <div class="mt-4">
                                <label class="block font-medium text-sm text-gray-700">
                                    Contact Name
                                </label>
                                <input id="contactname" class="block mt-1 w-full form-control" type="text" name="contactname" value="{{ $user->contactname }}" required {{ $user->role == 'dropshipper' ? 'disabled' : ''}}>
                            </div>
                            <div class="mt-4">
                                <label class="block font-medium text-sm text-gray-700">
                                    Mobile Number
                                </label>
                                <input id="phone" class="block mt-1 w-full form-control" type="text" name="phone" value="{{ $user->phone }}" required {{ $user->role == 'dropshipper' ? 'disabled' : ''}}>
                            </div>
                            <div class="mt-4">
                                <label class="block font-medium text-sm text-gray-700">
                                    Email
                                </label>
                                <input id="email" class="block mt-1 w-full form-control" type="email" name="email" value="{{ $user->email }}" required {{ $user->role == 'dropshipper' ? 'disabled' : ''}}>
                            </div>
                            @if ($user->role == 'member')
                                <div class="mt-4">
                                    <label class="block font-medium text-sm text-gray-700">
                                        Upload Logo
                                    </label>
                                    <input type="file" onchange="previewFile(this);" class="block mt-1 w-full" name="logo" id="logo">
                                </div>
                            @endif

                            @if(!empty($user->logo))
                                <img id="previewImg" style="margin-top: 15px;" width="180" height="180" src="{{asset($user->logo)}}" alt="Placeholder">
                            @else
                                <img id="previewImg" style="margin-top: 15px;" width="180" height="180" src="{{asset('img/No_Image_Available.jpg')}}" alt="Placeholder">
                            @endif

                            @if ($user->role != 'dropshipper')
                                <div class="flex justify-end mt-4">
                                    <x-button tyoe="submit" color="blue">
                                        Update Profile
                                    </x-button>
                                </div>
                            @endif
                        </form>
                    </x-card.body>
                </x-card.card-default>
            </div>
            <div>
                <div class="grid grid-cols-1 gap-6">
                    <x-card.card-default>
                        <x-card.header>
                            <x-card.title>
                                {{ __('translation.Change Password') }}
                            </x-card.title>
                        </x-card.header>
                        <x-card.body>
                            <form method="POST" class="mx-2" action="{{ route('change_password') }}">
                                @csrf
                                @if ($errors->any())
                                    <div class="mb-4">
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
                                @if (session()->has('success'))
                                    <div class="mb-4 text-md font-medium text-green-600">
                                        {{ session()->get('success') }}
                                    </div>
                                @endif

                                @if (session()->has('error'))
                                    <div class="mb-4 text-md font-medium text-red-600">
                                        {{ session()->get('error') }}
                                    </div>
                                @endif
                                <div class="mt-4">
                                    <label class="block font-medium text-sm text-gray-700">
                                        Current Password
                                    </label>
                                    <x-input type="password" name="current-password"></x-input>
                                </div>
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

                                <div class="flex justify-end mt-4">
                                    <x-button color="blue">
                                        Change Password
                                    </x-button>
                                </div>
                            </form>
                        </x-card.body>
                    </x-card.card-default>

                    <x-card.card-default>
                        <x-card.header>
                            <x-card.title>
                                {{ __('translation.apps_settings') }}
                            </x-card.title>
                        </x-card.header>
                        <x-card.body>
                            <form method="POST" class="mx-2" action="{{ route('user.change-language') }}">
                                @csrf

                                @if (session('lang-success'))
                                    <x-alert-success>
                                        {{ session('lang-success') }}
                                    </x-alert-success>
                                @endif

                                @if (session('lang-error'))
                                    <x-alert-danger>
                                        {{ session('lang-error') }}
                                    </x-alert-danger>
                                @endif

                                <div class="mt-4">
                                    <label class="block font-medium text-sm text-gray-700">
                                        {{ __('translation.language') }}
                                    </label>
                                    <x-select name="lang" required>
                                        <option value="" disabled>
                                            {{ __('translation.select_language') }}
                                        </option>

                                        @foreach ($availablePrefLang as $value => $text)
                                            <option value="{{ $value }}" @if (Auth::user()->pref_lang == $value) selected @endif>
                                                {{ $text }}
                                            </option>
                                        @endforeach
                                    </x-select>
                                </div>

                                <div class="flex justify-end mt-4">
                                    <x-button type="submit" color="blue">
                                        {{ __('translation.update_settings') }}
                                    </x-button>
                                </div>
                            </form>
                        </x-card.body>
                    </x-card.card-default>

                </div>
            </div>
        </div>
    </div>


    <script type="text/javascript">
        function previewFile(input){
            var file = $("input[type=file]").get(0).files[0];

            if(file){
                var reader = new FileReader();

                reader.onload = function(){
                    $("#previewImg").attr("src", reader.result);
                }
                reader.readAsDataURL(file);
            }
        }
    </script>

</x-app-layout>
