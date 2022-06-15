<!DOCTYPE html>
<html lang="{{ config('app.locale') }}">
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
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap">

    @stack('top_css')
    <link rel="stylesheet" href="{{ asset('css/app.css?_=' . rand()) }}">

    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
    @routes
</head>

<body class="font-sans antialiased bg-gray-100">
    <main class="py-12">
        @yield('content')
    </main>

    @stack('bottom_js')
</body>
</html>
