<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />

        <title>{{ filled($title ?? null) ? $title.' - '.config('app.name', 'Laravel') : config('app.name', 'Laravel') }}</title>
        <meta name="description" content="{{ $metaDescription ?? 'Portal Penerimaan Murid Baru Stella Gracia School Kupang - Raih masa depan cerah bersama pendidikan berkualitas.' }}">

        <link rel="icon" href="/favicon.ico" sizes="any">
        <link rel="icon" href="/favicon.svg" type="image/svg+xml">
        <link rel="apple-touch-icon" href="/apple-touch-icon.png">

        @fonts

        @vite(['resources/css/app.css', 'resources/js/app.js'])

        @stack('styles')

        <style>[x-cloak] { display: none !important; }</style>
    </head>
    <body class="bg-surface text-on-surface font-body antialiased">
        @include('partials.landing-navbar')

        <main>
            {{ $slot }}
        </main>

        @include('partials.landing-footer')

        @fluxScripts
        @stack('scripts')
    </body>
</html>
