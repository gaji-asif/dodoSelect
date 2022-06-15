@extends('layouts.auth')

@section('title')
    {{ __('translation.Sign In') }}
@endsection

@section('content')
    <div class="flex justify-center mb-4">
        <img class="w-2/5 sm:w-40 h-auto" src="{{ asset('img/dodoselect.png') }}" alt="{{ config('app.name') }}">
    </div>
    <div class="w-4/5 sm:max-w-xs mx-auto">
        <x-card.card-default>
            <x-card.header>
                <x-card.title class="px-4 pt-2">
                    {{ __('translation.Sign In') }}
                </x-card.title>
            </x-card.header>
            <x-card.body>
                <div class="px-4">
                    @if(session('failed'))
                        <x-alert-danger>{{ session('failed') }}</x-alert-danger>
                    @endif

                    <form method="POST" action="{{ route('signin') }}">
                        @csrf

                        @if ($errors->any())
                            <div class="mb-4">
                                <div class="font-medium text-red-600">
                                    {{ $errors->first() }}
                                </div>
                            </div>
                        @endif

                        @if (Session::has('success'))
                            <div class="font-medium text-green-600">
                                Success Inserted
                            </div>
                            <div class="text-sm text-green-600">
                                Your data successfully being inserted
                            </div>
                        @endif
                        @if (Session::has('successs'))
                            <div class="text-sm text-green-600">
                                {{Session::get('successs')}}
                            </div>
                        @endif

                        <div class="mb-4">
                            <x-label>
                                {{ ucwords(__('translation.mobile_number')) }} <x-form.required-mark/>
                            </x-label>
                            <x-input type="text" name="phone" value="{{ old('phone') }}" />
                        </div>
                        <div class="mb-4">
                            <x-label>
                                {{ ucfirst(__('translation.password')) }} <x-form.required-mark/>
                            </x-label>
                            <x-input type="password" name="password" />
                        </div>

                        <div class="block mb-4">
                            <label for="remember_me" class="inline-flex items-center">
                                <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" name="remember">
                                <span class="ml-2 text-sm text-gray-600">{{ ucfirst(__('translation.remember_me')) }}</span>
                            </label>
                        </div>
                        <div class="flex items-center justify-between mb-8">
                            <div class="w-3/5">
                                <a href="{{ route('forget-password') }}" class="no-underline hover:underline text-sm text-blue-600">
                                    {{ __('translation.forgot_password') }}
                                </a>
                            </div>
                            <div class="w-2/5 text-right">
                                <x-button type="submit" color="blue" class="w-full">
                                    {{ __('translation.sign_in') }}
                                </x-button>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="w-full inline-flex items-center justify-center">
                                <span class="text-gray-700 mr-2">
                                    {{ ucfirst(__("translation.Do_not_have_an_account")) . '?' }}
                                </span>
                                <a href="{{ route('register') }}" class="no-underline hover:underline text-sm text-blue-600 font-bold">
                                    {{ ucfirst(__('translation.register')) }}
                                </a>
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
