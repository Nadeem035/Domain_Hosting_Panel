<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <script>
            (function () {
                var media = window.matchMedia('(prefers-color-scheme: dark)');
                function apply(dark) {
                    document.documentElement.classList.toggle('dark', dark);
                    document.documentElement.style.colorScheme = dark ? 'dark' : 'light';
                }
                apply(media.matches);
                media.addEventListener('change', function (e) { apply(e.matches); });
            })();
        </script>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div class="flex min-h-screen flex-col items-center justify-center bg-zinc-50 px-4 py-12 sm:px-6 dark:bg-zinc-950">
            <div class="w-full max-w-md">
                <a href="/" wire:navigate class="mb-8 flex items-center justify-center gap-3">
                    <x-application-logo class="h-11 w-11" />
                    <span class="text-xl font-bold tracking-tight text-zinc-900 dark:text-zinc-100">{{ config('app.name') }}</span>
                </a>

                <div class="card p-6 sm:p-8">
                    {{ $slot }}
                </div>

                <p class="mt-6 text-center text-xs text-zinc-400 dark:text-zinc-600">
                    Domains · Hosting · Renewals — in one place
                </p>
            </div>
        </div>
    </body>
</html>