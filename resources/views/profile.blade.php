<x-app-layout>
    <div class="space-y-6">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-zinc-900 dark:text-zinc-100">{{ __('Profile') }}</h1>
            <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Manage your account information, password, and session security.</p>
        </div>

        <div class="card p-6 sm:p-8">
            <livewire:profile.update-profile-information-form />
        </div>

        <div class="card p-6 sm:p-8">
            <livewire:profile.update-password-form />
        </div>

        <div class="card p-6 sm:p-8">
            <livewire:profile.delete-user-form />
        </div>
    </div>
</x-app-layout>