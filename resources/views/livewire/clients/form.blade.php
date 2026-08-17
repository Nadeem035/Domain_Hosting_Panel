<div>
    <x-page-heading :title="$client ? 'Edit client' : 'Add client'"
        subtitle="{{ $client ? 'Update details for ' . $client->name : 'Create a new client to attach services to.' }}">
        <x-slot:actions>
            @if ($client)
                <a href="{{ route('clients.show', $client) }}" wire:navigate class="btn-secondary">
                    <x-icon name="chevron-left" class="h-4 w-4" />
                    Back
                </a>
            @else
                <a href="{{ route('clients.index') }}" wire:navigate class="btn-secondary">
                    <x-icon name="chevron-left" class="h-4 w-4" />
                    All clients
                </a>
            @endif
        </x-slot:actions>
    </x-page-heading>

    <form wire:submit="save" class="mt-6 max-w-2xl space-y-6">
        <div class="card space-y-5 p-6 sm:p-8">
            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <x-input-label for="name" value="Client name" required />
                    <x-text-input id="name" wire:model="name" class="mt-1.5 w-full" placeholder="Jane Doe" />
                    <x-input-error :messages="$errors->get('name')" class="mt-1" />
                </div>
                <div>
                    <x-input-label for="company" value="Company" />
                    <x-text-input id="company" wire:model="company" class="mt-1.5 w-full" placeholder="Acme Ltd" />
                    <x-input-error :messages="$errors->get('company')" class="mt-1" />
                </div>
                <div>
                    <x-input-label for="email" value="Email" />
                    <x-text-input id="email" type="email" wire:model="email" class="mt-1.5 w-full" placeholder="jane@example.com" />
                    <x-input-error :messages="$errors->get('email')" class="mt-1" />
                </div>
                <div>
                    <x-input-label for="phone" value="Phone" />
                    <x-text-input id="phone" wire:model="phone" class="mt-1.5 w-full" placeholder="+1 555 000 1234" />
                    <x-input-error :messages="$errors->get('phone')" class="mt-1" />
                </div>
                <div class="sm:col-span-2">
                    <x-input-label for="address" value="Address" />
                    <x-text-input id="address" wire:model="address" class="mt-1.5 w-full" placeholder="Street, city, country" />
                    <x-input-error :messages="$errors->get('address')" class="mt-1" />
                </div>
                <div>
                    <x-input-label for="status" value="Status" />
                    <select id="status" wire:model="status" class="input mt-1.5">
                        @foreach ($statuses as $status)
                            <option value="{{ $status->value }}">{{ $status->label() }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('status')" class="mt-1" />
                </div>
            </div>

            <div>
                <x-input-label for="notes" value="Notes" />
                <textarea id="notes" wire:model="notes" rows="3" class="input mt-1.5"
                    placeholder="Anything worth remembering about this client…"></textarea>
                <x-input-error :messages="$errors->get('notes')" class="mt-1" />
            </div>

            <div class="flex items-center justify-end gap-2 border-t border-zinc-100 pt-5 dark:border-zinc-800">
                <a href="{{ $client ? route('clients.show', $client) : route('clients.index') }}" wire:navigate class="btn-secondary">Cancel</a>
                <button type="submit" class="btn-primary">
                    <x-icon name="check-circle" class="h-4 w-4" />
                    {{ $client ? 'Save changes' : 'Create client' }}
                </button>
            </div>
        </div>
    </form>
</div>