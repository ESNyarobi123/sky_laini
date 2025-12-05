<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'SKY LAINI')</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700,800,900" rel="stylesheet" />
    
    @stack('styles')
</head>
<body class="min-h-screen bg-gradient-to-br from-sky-50 via-sky-100 to-sky-200">
    @yield('content')
    @stack('scripts')
</body>
</html>