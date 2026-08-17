<div>
    <x-page-heading :title="$user ? 'Edit user' : 'Add user'"
        subtitle="{{ $user ? 'Update account details and role for ' . $user->name : 'Create a new account with a role.' }}">
        <x-slot:actions>
            <a href="{{ route('users.index') }}" wire:navigate class="btn-secondary">
                <x-icon name="chevron-left" class="h-4 w-4" />
                All users
            </a>
        </x-slot:actions>
    </x-page-heading>

    <form wire:submit="save" class="mt-6 max-w-2xl space-y-6">
        <div class="card space-y-5 p-6 sm:p-8">
            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <x-input-label for="name" value="Name" required />
                    <x-text-input id="name" wire:model="name" class="mt-1.5 w-full" placeholder="Jane Doe" />
                    <x-input-error :messages="$errors->get('name')" class="mt-1" />
                </div>
                <div>
                    <x-input-label for="email" value="Email" required />
                    <x-text-input id="email" type="email" wire:model="email" class="mt-1.5 w-full" placeholder="jane@example.com" />
                    <x-input-error :messages="$errors->get('email')" class="mt-1" />
                </div>
                <div>
                    <x-input-label for="password" value="Password" :required="!$user" />
                    <x-text-input id="password" type="password" wire:model="password" class="mt-1.5 w-full" autocomplete="new-password"
                        placeholder="{{ $user ? 'Leave blank to keep current' : '••••••••' }}" />
                    <x-input-error :messages="$errors->get('password')" class="mt-1" />
                </div>
                <div>
                    <x-input-label for="password_confirmation" value="Confirm password" :required="!$user" />
                    <x-text-input id="password_confirmation" type="password" wire:model="password_confirmation" class="mt-1.5 w-full" autocomplete="new-password" />
                    <x-input-error :messages="$errors->get('password_confirmation')" class="mt-1" />
                </div>
                <div>
                    <x-input-label for="company_name" value="Company" />
                    <x-text-input id="company_name" wire:model="company_name" class="mt-1.5 w-full" placeholder="Acme Ltd" />
                    <x-input-error :messages="$errors->get('company_name')" class="mt-1" />
                </div>
                <div>
                    <x-input-label for="role" value="Role" required />
                    <select id="role" wire:model="role" class="input mt-1.5">
                        @foreach ($roles as $role)
                            <option value="{{ $role->name }}">{{ ucfirst($role->name) }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('role')" class="mt-1" />
                </div>
                <div>
                    <x-input-label for="timezone" value="Timezone" />
                    <select id="timezone" wire:model="timezone" class="input mt-1.5">
                        @foreach ($timezones as $tz)
                            <option value="{{ $tz }}">{{ $tz }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('timezone')" class="mt-1" />
                </div>
                <div>
                    <x-input-label for="theme_preference" value="Theme" />
                    <select id="theme_preference" wire:model="theme_preference" class="input mt-1.5">
                        <option value="system">System</option>
                        <option value="light">Light</option>
                        <option value="dark">Dark</option>
                    </select>
                    <x-input-error :messages="$errors->get('theme_preference')" class="mt-1" />
                </div>
            </div>

            <div class="flex items-center justify-end gap-2 border-t border-zinc-100 pt-5 dark:border-zinc-800">
                <a href="{{ route('users.index') }}" wire:navigate class="btn-secondary">Cancel</a>
                <button type="submit" class="btn-primary">
                    <x-icon name="check-circle" class="h-4 w-4" />
                    {{ $user ? 'Save changes' : 'Create user' }}
                </button>
            </div>
        </div>
    </form>
</div>