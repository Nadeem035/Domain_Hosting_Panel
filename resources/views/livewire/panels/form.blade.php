<div>
    <x-page-heading :title="$panel ? 'Edit panel' : 'Add panel'"
        subtitle="{{ $panel ? 'Update details for ' . $panel->name : 'Register a server or control panel you work with.' }}">
        <x-slot:actions>
            @if ($panel)
                <a href="{{ route('panels.show', $panel) }}" wire:navigate class="btn-secondary">
                    <x-icon name="chevron-left" class="h-4 w-4" />
                    Back
                </a>
            @else
                <a href="{{ route('panels.index') }}" wire:navigate class="btn-secondary">
                    <x-icon name="chevron-left" class="h-4 w-4" />
                    All panels
                </a>
            @endif
        </x-slot:actions>
    </x-page-heading>

    <form wire:submit="save" class="mt-6 max-w-2xl space-y-6">
        <div class="card space-y-5 p-6 sm:p-8">
            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <x-input-label for="name" value="Panel name" required />
                    <x-text-input id="name" wire:model="name" class="mt-1.5 w-full" placeholder="Shared cPanel #1" />
                    <x-input-error :messages="$errors->get('name')" class="mt-1" />
                </div>
                <div>
                    <x-input-label for="type" value="Type" required />
                    <select id="type" wire:model="type" class="input mt-1.5">
                        @foreach ($types as $type)
                            <option value="{{ $type->value }}">{{ $type->label() }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('type')" class="mt-1" />
                </div>
                <div>
                    <x-input-label for="host" value="Host" />
                    <x-text-input id="host" wire:model="host" class="mt-1.5 w-full" placeholder="server1.example.com" />
                    <x-input-error :messages="$errors->get('host')" class="mt-1" />
                </div>
                <div>
                    <x-input-label for="ip_address" value="IP address" />
                    <x-text-input id="ip_address" wire:model="ip_address" class="mt-1.5 w-full" placeholder="203.0.113.10" />
                    <x-input-error :messages="$errors->get('ip_address')" class="mt-1" />
                </div>
                <div>
                    <x-input-label for="username" value="Panel username" />
                    <x-text-input id="username" wire:model="username" class="mt-1.5 w-full" placeholder="root" />
                    <x-input-error :messages="$errors->get('username')" class="mt-1" />
                </div>
                <div>
                    <x-input-label for="client_limit" value="Client limit" />
                    <x-text-input id="client_limit" type="number" min="0" wire:model="client_limit" class="mt-1.5 w-full" />
                    <p class="mt-1 text-xs text-zinc-400 dark:text-zinc-500">0 means unlimited.</p>
                    <x-input-error :messages="$errors->get('client_limit')" class="mt-1" />
                </div>
                <div class="sm:col-span-2">
                    <x-input-label for="login_url" value="Login URL" />
                    <x-text-input id="login_url" type="url" wire:model="login_url" class="mt-1.5 w-full" placeholder="https://panel.example.com:2083" />
                    <x-input-error :messages="$errors->get('login_url')" class="mt-1" />
                </div>
                <div class="sm:col-span-2">
                    <label class="flex cursor-pointer items-center gap-3">
                        <input type="checkbox" wire:model="is_active" class="checkbox">
                        <span>
                            <span class="block text-sm font-medium text-zinc-800 dark:text-zinc-200">Active panel</span>
                            <span class="block text-xs text-zinc-400 dark:text-zinc-500">Inactive panels can still be attached to existing services.</span>
                        </span>
                    </label>
                    <x-input-error :messages="$errors->get('is_active')" class="mt-1" />
                </div>
            </div>

            <div>
                <x-input-label for="notes" value="Notes" />
                <textarea id="notes" wire:model="notes" rows="3" class="input mt-1.5"
                    placeholder="Anything worth remembering about this panel…"></textarea>
                <x-input-error :messages="$errors->get('notes')" class="mt-1" />
            </div>

            <div class="flex items-center justify-end gap-2 border-t border-zinc-100 pt-5 dark:border-zinc-800">
                <a href="{{ $panel ? route('panels.show', $panel) : route('panels.index') }}" wire:navigate class="btn-secondary">Cancel</a>
                <button type="submit" class="btn-primary">
                    <x-icon name="check-circle" class="h-4 w-4" />
                    {{ $panel ? 'Save changes' : 'Create panel' }}
                </button>
            </div>
        </div>
    </form>
</div>