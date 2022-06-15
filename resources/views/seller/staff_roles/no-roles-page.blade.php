<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name') }}</title>

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">

    <!-- Fonts -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap">

    <style id="" media="all">
        @font-face {
            font-family: 'Maven Pro';
            font-style: normal;
            font-weight: 400;
            src: url(/fonts.gstatic.com/s/mavenpro/v22/7Au9p_AqnyWWAxW2Wk3GzWQI.woff2) format('woff2');
            unicode-range: U+0000-00FF, U+0131, U+0152-0153, U+02BB-02BC, U+02C6, U+02DA, U+02DC, U+2000-206F, U+2074, U+20AC, U+2122, U+2191, U+2193, U+2212, U+2215, U+FEFF, U+FFFD;
        }
        /* vietnamese */
        @font-face {
            font-family: 'Maven Pro';
            font-style: normal;
            font-weight: 900;
            src: url(/fonts.gstatic.com/s/mavenpro/v22/7Au9p_AqnyWWAxW2Wk3GwmQIAFg.woff2) format('woff2');
            unicode-range: U+0102-0103, U+0110-0111, U+0128-0129, U+0168-0169, U+01A0-01A1, U+01AF-01B0, U+1EA0-1EF9, U+20AB;
        }
        /* latin-ext */
        @font-face {
            font-family: 'Maven Pro';
            font-style: normal;
            font-weight: 900;
            src: url(/fonts.gstatic.com/s/mavenpro/v22/7Au9p_AqnyWWAxW2Wk3Gw2QIAFg.woff2) format('woff2');
            unicode-range: U+0100-024F, U+0259, U+1E00-1EFF, U+2020, U+20A0-20AB, U+20AD-20CF, U+2113, U+2C60-2C7F, U+A720-A7FF;
        }
        /* latin */
        @font-face {
            font-family: 'Maven Pro';
            font-style: normal;
            font-weight: 900;
            src: url(/fonts.gstatic.com/s/mavenpro/v22/7Au9p_AqnyWWAxW2Wk3GzWQI.woff2) format('woff2');
            unicode-range: U+0000-00FF, U+0131, U+0152-0153, U+02BB-02BC, U+02C6, U+02DA, U+02DC, U+2000-206F, U+2074, U+20AC, U+2122, U+2191, U+2193, U+2212, U+2215, U+FEFF, U+FFFD;
        }
    </style>

    <!-- Styles -->
    <link rel="stylesheet" href="{{ asset('css/app.css?_=' . rand()) }}">

    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}" defer></script>

    <!-- Custom stlylesheet -->
    <link rel="stylesheet"  href="{{ asset('css/errorpage.css')}}" />
    {{--    <link rel="stylesheet"  href="{{ asset('css/errorpage2.css')}}" />--}}
</head>

<body class="font-sans antialiased overflow-x-hidden">

<header class="w-full fixed top-0 left-0 bg-white shadow-md z-20">
    <nav class="w-full 2xl:max-w-7xl 2xl:mx-auto flex flex-row items-center justify-between" x-data="{ sidebarOpen: false }">
        <div class="w-full h-full fixed inset-0 z-20 transition-opacity duration-300 opacity-0 pointer-events-none"
             :class="{ '': sidebarOpen === true, 'opacity-0 pointer-events-none': sidebarOpen === false }"
             x-on:click="sidebarOpen = false">
            <div class="absolute w-full h-full bg-gray-900 bg-opacity-50 z-30"></div>
        </div>
        <div class="w-1/3 xl:w-72 xl:z-30">
            <div class="w-full h-full flex items-center justify-center">
                <a href="#" class="py-2">
                    <img src="{{ asset('img/dodoselect.png') }}" alt="{{ config('app.name') }}" class="h-9 w-auto">
                </a>
            </div>
        </div>
        <div class="w-1/3 xl:w-full xl:z-30 relative" x-data="{ dropdownOpen: false }">
            <div class="w-full flex justify-end">
                <button type="button" class="h-9 xl:h-10 px-7 xl:px-12 2xl:px-0 py-2 bg-transparent border-0 inline-flex items-center outline-none focus:outline-none cursor-pointer" x-on:click="dropdownOpen = !dropdownOpen" x-on:click.away="dropdownOpen = false">
                    <span class="relative">
                        <img src="{{ Auth::user()->avatar_url }}" alt="{{ Auth::user()->username }}" class="w-8 h-8 rounded-full">
                    </span>
                    <span class="hidden lg:block text-gray-800 ml-2">
                        {{ Auth::user()->username }}
                    </span>
                </button>
            </div>

            <div class="absolute top-10 right-8 2xl:right-0 left-auto w-48 py-1 border border-solid border-gray-300 shadow-lg bg-white hidden" :class="{ 'hidden' : dropdownOpen === false }">
{{--                <a href="{{ route('profile') }}" class="block px-5 py-2 text-gray-800 hover:bg-gray-100 no-underline">--}}
{{--                    {{ __('translation.Your Profile') }}--}}
{{--                </a>--}}
                <hr class="w-full border border-r-0 border-b-0 border-l-0 border-gray-200 my-1">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <a href="#" onClick="event.preventDefault(); this.closest('form').submit();" class="block px-5 py-2 text-gray-800 hover:bg-gray-100 no-underline">
                        Sign Out
                    </a>
                </form>
            </div>
        </div>
    </nav>
</header>
<main>
    <div id="notfound">
        <div class="notfound">
            <div class="notfound-404">
                <h1>404</h1>
            </div>
            <h1 class="font-weight-bold">You have not been assigned any role yet.</h1>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <a href="#" onClick="event.preventDefault(); this.closest('form').submit();">
                    Sign Out
                </a>
            </form>
        </div>
    </div>
</main>
</body>
</html>
