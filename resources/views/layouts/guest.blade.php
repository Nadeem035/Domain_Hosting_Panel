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
        <div class="flex min-h-screen bg-zinc-50 dark:bg-zinc-950">
            {{-- Brand panel (desktop) --}}
            <div class="relative hidden w-1/2 overflow-hidden bg-zinc-950 lg:block dark:border-r dark:border-zinc-800">
                <div class="pointer-events-none absolute inset-0">
                    <div class="absolute -left-24 -top-24 h-96 w-96 rounded-full bg-primary-600/30 blur-3xl"></div>
                    <div class="absolute bottom-0 right-0 h-[28rem] w-[28rem] translate-x-1/3 translate-y-1/3 rounded-full bg-primary-400/20 blur-3xl"></div>
                    <div class="absolute left-1/2 top-1/2 h-64 w-64 -translate-x-1/2 -translate-y-1/2 rounded-full bg-fuchsia-500/10 blur-3xl"></div>
                </div>

                <div class="relative flex h-full flex-col justify-between p-12">
                    <a href="/" wire:navigate class="flex items-center gap-3">
                        <x-application-logo class="h-10 w-10" />
                        <span class="text-lg font-bold tracking-tight text-white">{{ config('app.name') }}</span>
                    </a>

                    <div>
                        <h2 class="max-w-md text-3xl font-bold leading-tight text-white">
                            Domains & hosting renewals, tracked so you never miss one.
                        </h2>
                        <p class="mt-4 max-w-md text-sm leading-relaxed text-zinc-400">
                            Manage your reseller portfolio — clients, services, invoices, and renewal reminders in a single, elegant workspace.
                        </p>

                        <ul class="mt-8 space-y-3">
                            @foreach ([
                                ['check-circle', 'Renewal alerts before services lapse'],
                                ['clock', 'Six-month revenue outlook at a glance'],
                                ['users', 'Full client, service and hosting overview'],
                            ] as [$icon, $text])
                                <li class="flex items-center gap-3 text-sm text-zinc-300">
                                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-white/10 backdrop-blur">
                                        <x-icon :name="$icon" class="h-4 w-4 text-primary-300" />
                                    </span>
                                    {{ $text }}
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    <p class="text-xs text-zinc-500">
                        Domains · Hosting · Renewals — in one place
                    </p>
                </div>
            </div>

            {{-- Form panel --}}
            <div class="flex w-full flex-col items-center justify-center px-4 py-12 sm:px-6 lg:w-1/2">
                <div class="w-full max-w-md">
                    <a href="/" wire:navigate class="mb-8 flex items-center justify-center gap-3 lg:hidden">
                        <x-application-logo class="h-11 w-11" />
                        <span class="text-xl font-bold tracking-tight text-zinc-900 dark:text-zinc-100">{{ config('app.name') }}</span>
                    </a>

                    <div class="card p-6 sm:p-8">
                        {{ $slot }}
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>