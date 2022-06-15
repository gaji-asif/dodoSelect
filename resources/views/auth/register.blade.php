@extends('layouts.auth')

@section('title')
    {{ __('translation.Register') }}
@endsection

@push('top_css')
<style type="text/css">
    @media screen and (max-width: 600px) {
        .logo_main {
            width: 50%;
            margin-bottom: 25px;
        }

        .col-lg-6 {
            margin-bottom: 8px;
        }

        .username_2nd {
            width: 50%;
            float: left;
            margin-bottom: 8px;
        }

        .username_1st {
            width: 50%;
            float: left;
            margin-bottom: 20px;
        }
    }
</style>
@endpush

@section('content')
    <div class="flex justify-center mb-4">
        <img class="w-2/5 sm:w-40 h-auto" src="{{ asset('img/dodoselect.png') }}" alt="{{ config('app.name') }}">
    </div>
    <div class="w-4/5 sm:max-w-xs md:max-w-xl mx-auto">
        <x-card.card-default>
            <x-card.header>
                <x-card.title class="px-4 pt-2">
                    {{ __('translation.Register') }}
                </x-card.title>
            </x-card.header>
            <x-card.body>
                <div class="px-4">
                    <form method="POST" action="{{ route('register') }}">
                        @csrf

                        @if(session('failed'))
                            <x-alert-danger class="mb-4">{{ session('failed') }}</x-alert-danger>
                        @endif

                        @if ($errors->any())
                            <x-alert-danger class="mb-4">
                                <ul class=" list-disc list-inside text-sm text-red-600">
                                    @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </x-alert-danger>
                        @endif
                        @if(session('password_error'))
                            <x-alert-danger class="mb-4">
                                <ul class=" list-disc list-inside text-sm text-red-600">
                                    <li>{{ session('password_error') }}</li>
                                </ul>
                            </x-alert-danger>
                        @endif

                        <!-- Shop Id -->
                        {{-- <div>
                            <x-label for="shop_id">Shop Id</x-label>
                            <x-input id="shop_id" class="block form-control w-full" type="text" name="shop_id" :value="old('shop_id')" required autofocus />
                        </div> --}}

                        <div class="mb-10">
                            <div class="flex flex-row items-center justify-between mb-2">
                                <h2 class="block whitespace-nowrap text-gray-600 text-base">
                                    {{ __('translation.Company Details') }}
                                </h2>
                                <hr class="w-full ml-3 border border-r-0 border-b-0 border-l-0 border-gray-50">
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-x-6">
                                <div>
                                    <div class="flex flex-row items-center justify-between">
                                        <div class="w-1/2">
                                            <x-label for="username">
                                                User Name <x-form.required-mark/>
                                            </x-label>
                                            <x-input type="text" name="username" id="username" :value="old('username')" placeholder="Username" required/>
                                        </div>
                                        <div class="w-1/2 ml-2">
                                            <div class="text-right">
                                                <span class="relative top-3">.dodotracking.com</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <x-label for="shopname">
                                        Shop Name <x-form.required-mark/>
                                    </x-label>
                                    <x-input type="text" name="name" id="name" :value="old('name')" placeholder="Enter Your Shop Name" required/>
                                </div>
                            </div>
                        </div>

                        <div class="mb-10">
                            <div class="flex flex-row items-center justify-between mb-3">
                                <h2 class="block whitespace-nowrap text-gray-600 text-base">
                                    {{ __('translation.Contact Details') }}
                                </h2>
                                <hr class="w-full ml-3 border border-r-0 border-b-0 border-l-0 border-gray-50">
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-x-6">
                                <div>
                                    <x-label for="contactname">
                                        {{ __('translation.Contact Name') }} <x-form.required-mark/>
                                    </x-label>
                                    <x-input id="contactname" class="block mt-1 w-full" placeholder="Contact Name" type="text" name="contactname" :value="old('contactname')" required />
                                </div>
                                <div>
                                    <x-label for="phone">
                                        {{ __('translation.Mobile Number') }} <x-form.required-mark/>
                                    </x-label>
                                    <x-input id="phone" class="block mt-1 w-full" placeholder="Mobile Number" type="text" name="phone" :value="old('phone')" required />
                                </div>
                                <div>
                                    <x-label for="email">
                                        {{ __('translation.Email') }} <x-form.required-mark/>
                                    </x-label>
                                    <x-input id="email" class="block mt-1 w-full" placeholder="Email" type="email" name="email" :value="old('email')" required />
                                </div>
                                <div class="col-lg-6">
                                    <x-label for="lineid">
                                        {{ __('translation.Line ID') }} <x-form.required-mark/>
                                    </x-label>
                                    <x-input id="lineid" class="block mt-1 w-full" placeholder="Line ID" type="text" name="lineid" :value="old('lineid')" required />
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <div class="flex flex-row items-center justify-between mb-2">
                                <h2 class="block whitespace-nowrap text-gray-600 text-base">
                                    {{ __('translation.Auth Details') }}
                                </h2>
                                <hr class="w-full ml-3 border border-r-0 border-b-0 border-l-0 border-gray-50">
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-x-6">
                                <div>
                                    <x-label for="password">
                                        Password <x-form.required-mark/>
                                    </x-label>
                                    <x-input id="password" class="block mt-1 w-full" placeholder="" type="password" name="password" required autocomplete="password" />
                                </div>
                                <div>
                                    <x-label for="password_confirmation">
                                        Confirm Password <x-form.required-mark/>
                                    </x-label>
                                    <x-input id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" placeholder="" required />
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="remember_me" class="inline-flex items-center">
                                <input type="checkbox" name="remember" id="remember_me" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required />
                                <span class="ml-2 text-sm text-gray-600">
                                    {{ __('translation.I accept the') }}
                                </span>
                                <a href="#" class="ml-1 text-blue-600 no-underline hover:underline">
                                    {{ __('translation.terms and conditions') }}
                                </a>.
                            </label>
                        </div>
                        <div class="flex flex-col md:flex-row items-center justify-between mb-4">
                            <div class="order-first md:order-last w-full md:w-1/2 text-right mb-6 md:mb-0">
                                <x-button type="submit" color="blue">
                                    {{ __('translation.Register') }}
                                </x-button>
                            </div>
                            <div class="order-last md:order-first w-full md:w-1/2">
                                <div class="w-full inline-flex items-center justify-center md:justify-start">
                                    <span class="text-gray-700 mr-2">
                                        Already registered?
                                    </span>
                                    <a href="{{ route('signin') }}" class="no-underline hover:underline text-sm text-blue-600 font-bold">
                                        {{ __('translation.Sign In') }}
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </x-card.body>
        </x-card.card-default>
    </div>
    <div class="mt-5 mb-10">
        @include('layouts.footer-lang-switcher')
    </div>
@endsection
