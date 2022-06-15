<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name') }} - Verify Mobile</title>

    <!-- Fonts -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap">

    <!-- Styles -->
    <link rel="stylesheet" href="{{ asset('css/app.css?_=' . rand()) }}">

    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}" defer></script>
</head>

<body class="font-sans antialiased">
    <div class="h-9/12 md:h-screen bg-gray-200">
        <main>
            <div class="h-screen w-screen py-12 flex items-center justify-center flex-col">
                <div class="flex md:w-1/5 justify-center">
                    <img class="w-6/12 md:w-4/5" src="{{ asset('img/dodoselect.png') }}" alt="">
                </div>
                <div class="px-8 mt-6">
                    <div class="bg-white rounded-md w-full overflow-hidden shadow">
                        <div class="p-6 md:px-10 rounded-lg bg-white">
                            <div class="md:flex md:justify-between md:items-center">
                                <div>
                                    <h2 class="text-xl text-gray-800 font-bold leading-tight" style="margin-left: 10px;">Verify Mobile</h2>
                                </div>
                            </div>
                            <div class="line my-4 mx-2 relative">
                                @if(session('failed'))
                                <x-alert-danger>{{ session('failed') }}</x-alert-danger>
                                @endif
                                <form method="POST" action="{{ route('get-otp') }}">
                                    @csrf

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

                                    @if (Session::has('success'))
                                        <div class="mb-4 font-medium text-green-600">
                                            {{ session('success') }}
                                        </div>
                                    @endif

                                    @if(Session::has('user_phone'))
                                    <input type="hidden" name="for_what" value="1">
                                    @elseif(Session::has('user_phone1'))
                                    <input type="hidden" name="for_what" value="2">
                                    @endif

                                    <div>
                                        <x-label>Enter your 4 digit otp here</x-label>
                                        <x-input type="text" name="otp" placeholder="xxxx"></x-input>
                                    </div>

                                    <div class="flex items-center justify-end mt-6">

                                        <x-button color="blue" class="ml-4" style="margin: 0 auto; margin-top: 20px;">
                                            {{ __('translation.Submit') }}
                                        </x-button>
                                    </div>
                                </form>

                                <div class="flex items-center  mt-3">
                                        <a style="margin: 0 auto; font-size: 15px;" class="underline text-sm text-gray-600 hover:text-gray-900"
                                            href="{{ url('signin') }}">
                                            Go to login page
                                        </a>


                                    </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>

</html>
