<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ isset($title) ? $title . ' — ' . config('app.name') : config('app.name') }}</title>

        <script>
            (function () {
                var pref = @json(auth()->user()?->theme_preference ?? 'system');
                var root = document.documentElement;
                var media = window.matchMedia('(prefers-color-scheme: dark)');
                function apply(dark) { root.classList.toggle('dark', dark); }
                if (pref === 'system') {
                    apply(media.matches);
                    media.addEventListener('change', function (e) { apply(e.matches); });
                } else {
                    apply(pref === 'dark');
                }
                root.style.colorScheme = pref === 'system' ? (media.matches ? 'dark' : 'light') : pref;
            })();
        </script>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @stack('head')
    </head>
    <body class="font-sans antialiased">
        @php
            $nav = [
                ['name' => 'Dashboard', 'icon' => 'dashboard', 'route' => 'dashboard', 'pattern' => 'dashboard'],
                ['name' => 'Clients', 'icon' => 'clients', 'route' => 'clients.*', 'pattern' => 'clients'],
                ['name' => 'Services', 'icon' => 'services', 'route' => 'services.*', 'pattern' => 'services'],
                ['name' => 'Panels & Plans', 'icon' => 'panels', 'route' => 'panels.*', 'pattern' => 'panels'],
                ['name' => 'Reports', 'icon' => 'reports', 'route' => 'reports.*', 'pattern' => 'reports'],
                ['name' => 'Settings', 'icon' => 'settings', 'route' => 'settings.*', 'pattern' => 'settings'],
            ];
        @endphp

        <div x-data="{
            mobileOpen: false,
            collapsed: localStorage.getItem('sidebar-collapsed') === '1',
            toggleCollapsed() {
                this.collapsed = !this.collapsed;
                localStorage.setItem('sidebar-collapsed', this.collapsed ? '1' : '0');
            },
        }" class="min-h-screen">
            {{-- Mobile drawer backdrop --}}
            <div x-show="mobileOpen" x-transition.opacity @click="mobileOpen = false"
                class="fixed inset-0 z-40 bg-zinc-950/40 backdrop-blur-sm lg:hidden"></div>

            {{-- Sidebar --}}
            <aside
                :class="collapsed ? 'lg:w-[4.5rem]' : 'lg:w-64'"
                class="fixed inset-y-0 left-0 z-50 flex w-64 -translate-x-full flex-col border-r border-zinc-200/80 bg-white transition-[width,transform] duration-200 ease-in-out lg:translate-x-0 dark:border-zinc-800 dark:bg-zinc-950"
                :class="mobileOpen ? 'translate-x-0' : ''"
                aria-label="Sidebar">
                {{-- Brand --}}
                <div class="flex h-16 shrink-0 items-center gap-3 border-b border-zinc-100 px-5 dark:border-zinc-800/80">
                    <a href="{{ route('dashboard') }}" wire:navigate class="flex items-center gap-3">
                        <x-application-logo class="h-9 w-9 shrink-0" />
                        <span x-show="!collapsed" x-transition.opacity
                            class="truncate text-[15px] font-bold tracking-tight text-zinc-900 dark:text-zinc-100">
                            {{ config('app.name') }}
                        </span>
                    </a>
                </div>

                {{-- Collapse toggle (desktop) --}}
                <button @click="toggleCollapsed()"
                    class="absolute -right-3 top-[4.5rem] hidden h-7 w-7 items-center justify-center rounded-full border border-zinc-200 bg-white text-zinc-400 shadow-sm transition hover:text-zinc-700 lg:flex dark:border-zinc-700 dark:bg-zinc-900 dark:hover:text-zinc-200"
                    title="Toggle sidebar">
                    <x-icon :name="'chevron-left'" class="h-4 w-4"
                        ::class="collapsed ? 'rotate-180' : ''" />
                </button>

                {{-- Nav --}}
                <nav class="flex-1 space-y-1 overflow-y-auto px-3 py-4" x-data>
                    @foreach ($nav as $item)
                        @php $active = request()->routeIs($item['pattern']) || request()->routeIs($item['pattern'] . '.*'); @endphp
                        <a href="{{ route($item['route'] === 'dashboard' ? 'dashboard' : $item['route']) }}" wire:navigate
                            title="{{ $item['name'] }}"
                            class="group flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition
                                {{ $active ? 'bg-primary-50 text-primary-700 dark:bg-primary-500/10 dark:text-primary-300' : 'text-zinc-600 hover:bg-zinc-100 hover:text-zinc-900 dark:text-zinc-400 dark:hover:bg-zinc-800/70 dark:hover:text-zinc-100' }}">
                            <x-icon :name="$item['icon']"
                                :class="$active ? 'h-5 w-5 shrink-0 text-primary-600 dark:text-primary-400' : 'h-5 w-5 shrink-0 text-zinc-400 transition group-hover:text-zinc-600 dark:text-zinc-500 dark:group-hover:text-zinc-300'" />
                            <span x-show="!collapsed" x-transition.opacity class="truncate">{{ $item['name'] }}</span>
                            @if ($active)
                                <span x-show="!collapsed"
                                    class="ml-auto h-1.5 w-1.5 rounded-full bg-primary-500"></span>
                            @endif
                        </a>
                    @endforeach
                </nav>

                {{-- Sidebar footer --}}
                <div class="border-t border-zinc-100 p-4 dark:border-zinc-800/80">
                    <div x-show="!collapsed" class="rounded-xl bg-zinc-50 p-3 dark:bg-zinc-900">
                        <p class="text-xs font-semibold text-zinc-700 dark:text-zinc-300">{{ auth()->user()?->name }}</p>
                        <p class="mt-0.5 truncate text-xs text-zinc-500 dark:text-zinc-500">{{ auth()->user()?->company_name ?? auth()->user()?->email }}</p>
                    </div>
                    <div x-show="collapsed" class="flex justify-center">
                        <div class="flex h-9 w-9 items-center justify-center rounded-full bg-primary-100 text-sm font-bold text-primary-700 dark:bg-primary-500/15 dark:text-primary-300">
                            {{ strtoupper(substr(auth()->user()?->name ?? '?', 0, 1)) }}
                        </div>
                    </div>
                </div>
            </aside>

            {{-- Main column --}}
            <div class="flex min-h-screen flex-col lg:pl-64" :class="collapsed ? 'lg:pl-[4.5rem]' : 'lg:pl-64'">
                {{-- Topbar --}}
                <header class="sticky top-0 z-30 border-b border-zinc-200/80 bg-zinc-50/80 backdrop-blur-md dark:border-zinc-800 dark:bg-zinc-950/80">
                    <div class="flex h-16 items-center gap-3 px-4 sm:px-6 lg:px-8">
                        <button @click="mobileOpen = true" class="rounded-lg p-2 text-zinc-500 transition hover:bg-zinc-100 hover:text-zinc-900 lg:hidden dark:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-zinc-100" aria-label="Open sidebar">
                            <x-icon name="menu" class="h-5 w-5" />
                        </button>

                        <livewire:components.global-search />

                        <div class="ml-auto flex items-center gap-1.5">
                            <livewire:components.notification-bell />
                            <livewire:components.theme-switcher />
                            <livewire:components.user-menu />
                        </div>
                    </div>
                </header>

                {{-- Page content --}}
                <main class="mx-auto w-full max-w-7xl flex-1 px-4 py-8 sm:px-6 lg:px-8">
                    {{ $slot }}
                </main>

                <footer class="mx-auto w-full max-w-7xl px-4 pb-6 sm:px-6 lg:px-8">
                    <p class="text-xs text-zinc-400 dark:text-zinc-600">
                        {{ config('app.name') }} — never miss a renewal.
                    </p>
                </footer>
            </div>
        </div>

        {{-- Global toasts --}}
        <div x-data="{
            toasts: [],
            addToast($event) {
                const id = Date.now() + Math.random();
                this.toasts.push({ id, message: $event.detail.message, type: $event.detail.type ?? 'success' });
                setTimeout(() => { this.toasts = this.toasts.filter(t => t.id !== id); }, 4000);
            },
        }" @toast.window="addToast($event)"
            class="pointer-events-none fixed bottom-5 right-5 z-[60] flex w-80 flex-col gap-2">
            <template x-for="toast in toasts" :key="toast.id">
                <div class="pointer-events-auto flex items-start gap-3 rounded-xl border p-4 shadow-card backdrop-blur"
                    :class="toast.type === 'error'
                        ? 'border-rose-200 bg-rose-50/95 dark:border-rose-800/60 dark:bg-rose-950/90'
                        : 'border-zinc-200 bg-white/95 dark:border-zinc-700/80 dark:bg-zinc-900/95'">
                    <x-icon :name="'check-circle'" class="h-5 w-5 shrink-0 text-emerald-500"
                        x-show="toast.type !== 'error'" />
                    <x-icon :name="'exclamation-triangle'" class="h-5 w-5 shrink-0 text-rose-500"
                        x-show="toast.type === 'error'" />
                    <p class="text-sm font-medium text-zinc-800 dark:text-zinc-100"
                        x-text="toast.message"></p>
                </div>
            </template>
        </div>
    </body>
</html>