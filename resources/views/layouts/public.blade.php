<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white dark:bg-zinc-800 antialiased">
        <div class="max-w-5xl mx-auto px-4 py-8">
            {{ $slot }}
        </div>
        @fluxScripts
    </body>
</html>
