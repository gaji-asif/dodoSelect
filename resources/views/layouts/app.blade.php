<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>
        {{ config('app.name') }}
        @hasSection ('title')
        - @yield('title')
        @endif
    </title>
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">

    <link rel="preconnect" href="https://fonts.googleapis.com/" crossorigin>
    <link rel="preconnect" href="https://cdn.jsdelivr.net/" crossorigin>
    <link rel="preconnect" href="https://cdnjs.cloudflare.com/" crossorigin>
    <link rel="preconnect" href="https://cdn.datatables.net/" crossorigin>
    <link rel="preconnect" href="https://code.jquery.com/" crossorigin>
    <link rel="dns-prefetch" href="https://fonts.googleapis.com/">
    <link rel="dns-prefetch" href="https://cdn.jsdelivr.net/">
    <link rel="dns-prefetch" href="https://cdnjs.cloudflare.com/">
    <link rel="dns-prefetch" href="https://cdn.datatables.net/">
    <link rel="dns-prefetch" href="https://code.jquery.com/">

    <!-- Fonts and Icons -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@5.15.3/css/all.min.css">

    <!-- Styles -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.24/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.24/css/dataTables.bootstrap4.min.css">

    @stack('top_css')
    <link rel="stylesheet" href="{{ asset('css/app.css?_=' . rand()) }}">

    @stack('bottom_css')

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
    <script src="{{ asset('js/base.js?_=' . rand()) }}"></script>
    <script src="{{ asset('js/app.js') }}" defer></script>
    @stack('top_js')

    <!-- Chart -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@2.8.0"></script>

    @routes
</head>

<body class="font-sans antialiased bg-white 2xl:bg-gray-100">
    @include('layouts.sidebar-navigation')

    <main class="min-h-screen mt-1 xl:mt-0 2xl:-mt-2 xl:ml-60 pb-16 lg:pb-0 bg-gray-100 2xl:bg-transparent">
        {{-- @include('layouts.navigation') --}}

        <!-- Page Content -->
        <div class="w-full 2xl:max-w-6xl 2xl:mx-auto 2xl:pl-12">
            <div class="w-full grid gap-x-8 gap-y-8 grid-cols-12 py-8 xl:pt-5 md:pb-12 px-5 justify-center">
                @if(session('registration-success'))
                    <div class="w-full  col-span-12 md:col-span-12">
                        <x-alert-success >{{ session('registration-success') }}</x-alert-success>
                    </div>
                @endif
                {{ $slot }}
            </div>
        </div>
    </main>


    @if (request()->routeIs('dashboard'))
        @include('elements.mobile-quick-links')
    @endif


    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery-slimscroll@1.3.8/jquery.slimscroll.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js"></script>
    <script src="{{ asset('js/dodo-modal.js?_=' . rand()) }}"></script>
    <script src="{{ asset('js/sidebar-nav.js?_' . rand()) }}"></script>
    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
    </script>

    @stack('bottom_js')

</body>
</html>
