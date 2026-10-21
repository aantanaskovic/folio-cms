<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ isset($title) ? "{$title} — " : '' }}{{ config('app.name') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>

<body class="min-h-screen bg-gray-50 antialiased">
    <x-navigation.dashboard-header />

    <main class="mx-auto w-full max-w-7xl px-6 py-10 lg:px-8">
        {{ $slot }}
    </main>

    @livewireScripts
</body>

</html>
