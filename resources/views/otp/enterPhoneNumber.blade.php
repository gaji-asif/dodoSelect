@extends('layouts.auth')

@section('title')
    {{ __('translation.Forgot Password') }}
@endsection

@section('content')
    <div class="flex justify-center mb-4">
        <img class="w-2/5 sm:w-40 h-auto" src="{{ asset('img/dodoselect.png') }}" alt="{{ config('app.name') }}">
    </div>
    <div class="w-4/5 sm:max-w-xs mx-auto">
        <x-card.card-default>
            <x-card.header>
                <x-card.title class="px-4 pt-2">
                    {{ __('translation.Forgot Password') }}
                </x-card.title>
            </x-card.header>
            <x-card.body>
                <div class="px-4">

                    @if(session('failed'))
                        <x-alert-danger>{{ session('failed') }}</x-alert-danger>
                    @endif

                    <form method="POST" action="{{ route('get-phone') }}">
                        @csrf

                        @if ($errors->any())
                            <div>
                                <div class="font-medium text-red-600">
                                    {{ __('translation.Error') }}
                                </div>

                                <ul class="mt-3 list-disc list-inside text-sm text-red-600">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        @if (Session::has('success'))
                            <div class="font-medium text-green-600">
                                Success
                            </div>
                            <div class="text-sm text-green-600">
                                {{ __('translation.The Code has been sent to your mobile number.') }}
                            </div>
                        @endif

                        <div class="mb-4">
                            <p class="text-gray-700 text-xs">
                                We'll send secret code to reset your password.
                            </p>
                        </div>
                        <div class="mb-4">
                            <x-label>
                                Enter Your  Mobile Number <x-form.required-mark/>
                            </x-label>
                            <x-input type="text" name="phone" />
                        </div>
                        <div class="flex items-center justify-between mb-4">
                            <div class="w-1/2">
                                <a href="{{ route('signin') }}" class="no-underline hover:underline text-sm text-blue-600">
                                    {{ __('translation.Back to sign in') }}
                                </a>
                            </div>
                            <div class="w-1/2 text-right">
                                <x-button type="submit" color="blue">
                                    {{ __('translation.Send Code') }}
                                </x-button>
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
