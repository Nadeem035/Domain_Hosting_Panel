<div>
    <x-page-heading title="Settings" subtitle="Personalise your workspace and control how renewal reminders reach you." />

    <form wire:submit="save" class="mt-6 max-w-2xl space-y-6">
        {{-- Appearance --}}
        <div class="card p-6">
            <h2 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Appearance</h2>
            <p class="mt-0.5 text-xs text-zinc-400 dark:text-zinc-500">How the app looks on this account, on every device.</p>

            <div class="mt-4 grid gap-3 sm:grid-cols-3">
                @foreach ([
                    'light' => ['sun', 'Light'],
                    'dark' => ['moon', 'Dark'],
                    'system' => ['globe', 'System'],
                ] as $value => [$icon, $label])
                    <label class="cursor-pointer">
                        <input type="radio" name="theme" value="{{ $value }}" wire:model.live="theme" class="peer sr-only">
                        <span class="flex items-center gap-2 rounded-lg border border-zinc-200 bg-white px-4 py-3 text-sm font-medium text-zinc-600 transition peer-checked:border-primary-500 peer-checked:bg-primary-50 peer-checked:text-primary-700 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300 dark:peer-checked:border-primary-400 dark:peer-checked:bg-primary-500/10 dark:peer-checked:text-primary-300">
                            <x-icon :name="$icon" class="h-4 w-4" />
                            {{ $label }}
                        </span>
                    </label>
                @endforeach
            </div>
            <x-input-error :messages="$errors->get('theme')" class="mt-2" />
        </div>

        {{-- Locale --}}
        <div class="card p-6">
            <h2 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Locale & currency</h2>
            <p class="mt-0.5 text-xs text-zinc-400 dark:text-zinc-500">Used for dates, reminders and how prices are displayed.</p>

            <div class="mt-4 grid gap-4 sm:grid-cols-2">
                <div>
                    <x-input-label for="timezone" value="Timezone" />
                    <select id="timezone" wire:model="timezone" class="input mt-1.5">
                        @foreach ($this->timezones() as $tz)
                            <option value="{{ $tz }}">{{ $tz }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('timezone')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="currency" value="Default currency" />
                    <x-text-input id="currency" wire:model="currency" class="mt-1.5 w-full uppercase" maxlength="3" placeholder="USD" />
                    <x-input-error :messages="$errors->get('currency')" class="mt-2" />
                </div>
            </div>
        </div>

        {{-- Email reminders --}}
        <div class="card p-6">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h2 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Email reminders</h2>
                    <p class="mt-0.5 text-xs text-zinc-400 dark:text-zinc-500">Renewal alerts are also stored in-app; emails are sent by the daily reminder check.</p>
                </div>
                <label class="relative inline-flex cursor-pointer items-center">
                    <input type="checkbox" wire:model="emailEnabled" class="peer sr-only">
                    <span class="h-6 w-11 rounded-full bg-zinc-200 transition peer-checked:bg-primary-600 after:absolute after:left-0.5 after:top-0.5 after:h-5 after:w-5 after:rounded-full after:bg-white after:shadow-sm after:transition peer-checked:after:translate-x-5 dark:bg-zinc-700"></span>
                </label>
            </div>

            <div class="mt-4 space-y-2">
                @foreach (\App\Enums\ReminderTier::cases() as $tier)
                    <label class="flex cursor-pointer items-center justify-between rounded-lg border border-zinc-200 px-4 py-3 transition hover:bg-zinc-50 dark:border-zinc-700 dark:hover:bg-zinc-800/60">
                        <span class="flex items-center gap-3">
                            <x-tier-badge tier="{{ $tier->value }}" :label="$tier->label()" />
                            <span class="text-xs text-zinc-400 dark:text-zinc-500">
                                {{ match ($tier) {
                                    \App\Enums\ReminderTier::Expired => 'past the expiry date',
                                    \App\Enums\ReminderTier::Urgent => 'within 7 days',
                                    \App\Enums\ReminderTier::DueSoon => 'within 15 days',
                                    default => 'within 30 days',
                                } }}
                            </span>
                        </span>
                        <input type="checkbox" wire:model="emailTiers.{{ $tier->value }}" :disabled="!$emailEnabled"
                            class="rounded border-zinc-300 text-primary-600 focus:ring-primary-500/30 dark:border-zinc-600 dark:bg-zinc-800" :disabled="!$emailEnabled">
                    </label>
                @endforeach
            </div>
            <x-input-error :messages="$errors->get('emailTiers')" class="mt-2" />
        </div>

        <div class="flex justify-end">
            <button type="submit" class="btn-primary">
                <x-icon name="check-circle" class="h-4 w-4" />
                Save settings
            </button>
        </div>
    </form>
</div>