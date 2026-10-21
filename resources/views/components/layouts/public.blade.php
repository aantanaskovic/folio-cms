<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ isset($title) ? "{$title} — " : '' }}{{ config('app.name') }}</title>
    @if (isset($metaDescription) && $metaDescription)
        <meta name="description" content="{{ $metaDescription }}">
    @endif

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>


<body class="min-h-screen antialiased">
    <x-navigation.public-header />

    <main>
        {{ $slot }}
    </main>


    <x-navigation.public-footer />


    @livewireScripts
</body>


</html>
