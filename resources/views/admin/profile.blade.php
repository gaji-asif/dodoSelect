<x-app-layout>
    @section('title', 'Profile')
        {{-- profile --}}
        {{-- <x-card title="Profile" md="7">
            @if (Auth()->user()->role == 'member')

            <div class="mt-4 mx-2">
                <label class="block font-medium text-sm text-gray-700">
                    Shop Id
                </label>

                <x-input type="text" value="{{ Auth()->user()->shop_id }}" class="bg-gray-200 border border-gray-500"
                    disabled>
                </x-input>
            </div>

            @endif

            <div class="mt-4 mx-2">
                <label class="block font-medium text-sm text-gray-700">
                    Name
                </label>

                <x-input type="text" value="{{ Auth()->user()->name }}" class="bg-gray-200 border border-gray-500"
                    disabled>
                </x-input>
            </div>

            <div class="mt-4 mx-2">
                <label class="block font-medium text-sm text-gray-700">
                    Email
                </label>

                <x-input type="text" value="{{ Auth()->user()->email }}" class="bg-gray-200 border border-gray-500"
                    disabled>
                </x-input>
            </div>
            @if (Auth()->user()->level == 'member')
                <div class="mt-4 mx-2">
                    <label class="block font-medium text-sm text-gray-700">
                        Shop Code
                    </label>

                    <x-input type="text" value="{{ Auth()->user()->shop_id }}" class="bg-gray-200 border border-gray-500"
                        disabled>
                    </x-input>
                </div>
            @endif
        </x-card> --}}
        <x-card title="Profile" md="5">
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
                       User Name
                    </label>
                    <x-input id="username" class="block mt-1 w-full" type="text" name="username"  value="{{$user->username}}"  required   class="bg-gray-200 border border-gray-500" disabled/>
                </div>
                <div class="mt-4">
                    <label class="block font-medium text-sm text-gray-700">
                       Shop Name
                    </label>
                    <x-input id="name" class="block mt-1 w-full" type="text" name="name" value="{{$user->name}}" required />
                </div>
                <div class="mt-4">
                    <label class="block font-medium text-sm text-gray-700">
                       Contact Name
                    </label>
                    <x-input id="contactname" class="block mt-1 w-full" type="text" name="contactname" value="{{$user->contactname}}" required />
                </div>
                <div class="mt-4">
                    <label class="block font-medium text-sm text-gray-700">
                        Mobile Number
                    </label>
                    <x-input id="phone" class="block mt-1 w-full" type="text" name="phone" value="{{$user->phone}}" required />
                </div>
                <div class="mt-4">
                    <label class="block font-medium text-sm text-gray-700">
                       Email
                    </label>
                    <x-input id="email" class="block mt-1 w-full" type="email" name="email" value="{{$user->email}}" required />
                </div>
                <div class="mt-4">
                    <label class="block font-medium text-sm text-gray-700">
                        Line Id
                    </label>
                    <x-input id="lineid" class="block mt-1 w-full" type="text" name="lineid" value="{{$user->lineid}}" required />
                </div>
                @if ($user->role == 'member')
                    <div class="mt-4">
                        <label class="block font-medium text-sm text-gray-700">
                            Upload Logo
                        </label>
                        <input type="file" class="block mt-1 w-full" name="logo" id="logo">
                    </div>
                @endif


                <div class="flex justify-end mt-4">
                    <x-button tyoe="submit" color="blue">
                        Update Profile
                    </x-button>
                </div>
            </form>
        </x-card>
        {{-- change password --}}
        <x-card title="Change Password" md="5">
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
        </x-card>

    </x-app-layout>
