<div>
    <x-page-heading :title="$service ? 'Edit service' : 'Add service'"
        subtitle="{{ $service ? 'Update the details of this service.' : 'Register a domain or hosting service for one of your clients.' }}">
        <x-slot:actions>
            <a href="{{ $service ? route('services.show', $service) : route('services.index') }}" wire:navigate class="btn-secondary">
                <x-icon name="chevron-left" class="h-4 w-4" />
                {{ $service ? 'Back' : 'All services' }}
            </a>
        </x-slot:actions>
    </x-page-heading>

    <form wire:submit="save" class="mt-6 max-w-3xl space-y-6">
        <div class="card space-y-5 p-6 sm:p-8">
            <div class="grid gap-5 sm:grid-cols-2">
                {{-- Client --}}
                <div class="relative sm:col-span-2" wire:click.outside="$set('showClientDropdown', false)">
                    <x-input-label value="Client" required />
                    <div class="relative mt-1.5">
                        <x-text-input wire:model.live="clientSearch" wire:focus="$set('showClientDropdown', true)"
                            class="w-full !pr-10" placeholder="Search by name, email or company…" autocomplete="off" />
                        <x-icon name="search" class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-zinc-400" />
                    </div>
                    @if ($showClientDropdown)
                        <div class="absolute z-20 mt-1.5 w-full overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-lg dark:border-zinc-700 dark:bg-zinc-800">
                            <div class="max-h-64 overflow-auto">
                                @forelse ($this->clients as $client)
                                    <button type="button" wire:click="selectClient({{ $client->id }})"
                                        class="flex w-full items-center justify-between gap-2 px-4 py-2.5 text-left hover:bg-zinc-50 dark:hover:bg-zinc-700/60">
                                        <span class="min-w-0">
                                            <span class="block truncate text-sm font-medium text-zinc-800 dark:text-zinc-200">{{ $client->name }}</span>
                                            <span class="block truncate text-xs text-zinc-400">{{ $client->email ?: $client->company ?: '—' }}</span>
                                        </span>
                                        <span class="badge shrink-0 {{ $client->status->value === 'active' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-400' : 'bg-zinc-100 text-zinc-500 dark:bg-zinc-700 dark:text-zinc-300' }}">
                                            {{ $client->status->label() }}
                                        </span>
                                    </button>
                                @empty
                                    <div class="px-4 py-3 text-sm text-zinc-500 dark:text-zinc-400">
                                        No clients match.
                                        <button type="button" wire:click="openClientQuickCreate" class="font-semibold text-primary-600 hover:text-primary-500">Add a new client</button>
                                    </div>
                                @endforelse
                            </div>
                            <div class="border-t border-zinc-100 bg-zinc-50/60 px-4 py-2 dark:border-zinc-700 dark:bg-zinc-900/40">
                                <button type="button" wire:click="openClientQuickCreate"
                                    class="text-xs font-semibold text-primary-600 hover:text-primary-500">
                                    <x-icon name="plus" class="mr-1 inline h-3.5 w-3.5" />
                                    Add a new client
                                </button>
                            </div>
                        </div>
                    @endif
                    @if ($client_id)
                        <div class="mt-1.5 flex items-center gap-2">
                            <span class="badge bg-primary-100 text-primary-700 dark:bg-primary-500/15 dark:text-primary-300">{{ $clientSearch }}</span>
                            <button type="button" wire:click="clearClient" class="text-xs text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-300">Clear</button>
                        </div>
                    @endif
                    <x-input-error :messages="$errors->get('client_id')" class="mt-1" />
                </div>

                {{-- Type --}}
                <div>
                    <x-input-label for="type" value="Service type" required />
                    <select id="type" wire:model.live="type" class="input mt-1.5">
                        @foreach ($types as $type)
                            <option value="{{ $type->value }}">{{ $type->label() }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('type')" class="mt-1" />
                </div>

                {{-- Status --}}
                <div>
                    <x-input-label for="status" value="Status" required />
                    <select id="status" wire:model="status" class="input mt-1.5">
                        @foreach ($statuses as $status)
                            <option value="{{ $status->value }}">{{ $status->label() }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('status')" class="mt-1" />
                </div>

                {{-- Domain --}}
                <div class="sm:col-span-2">
                    <x-input-label for="domain_name" value="Domain name" :required="in_array($this->type, ['domain', 'both'])" />
                    <x-text-input id="domain_name" wire:model="domain_name" class="mt-1.5 w-full" placeholder="example.com" />
                    <x-input-error :messages="$errors->get('domain_name')" class="mt-1" />
                </div>

                @if (in_array($this->type, ['hosting', 'both']))
                    {{-- Panel --}}
                    <div>
                        <x-input-label for="panel_id" value="Panel" required />
                        <div class="flex gap-2">
                            <select id="panel_id" wire:model.live="panel_id" class="input mt-1.5 flex-1">
                                <option value="">— Select a panel —</option>
                                @foreach ($panels as $panel)
                                    <option value="{{ $panel->id }}">{{ $panel->name }}</option>
                                @endforeach
                            </select>
                            <button type="button" wire:click="openPanelQuickCreate" title="Add a new panel"
                                class="btn-secondary mt-1.5 !px-3">
                                <x-icon name="plus" class="h-4 w-4" />
                            </button>
                        </div>
                        <x-input-error :messages="$errors->get('panel_id')" class="mt-1" />
                    </div>

                    {{-- Plan --}}
                    <div class="relative" wire:click.outside="$set('showPlanDropdown', false)">
                        <x-input-label value="Hosting plan" />
                        <div class="relative mt-1.5">
                            <x-text-input wire:model.live="planSearch" wire:focus="$set('showPlanDropdown', true)"
                                class="w-full !pr-10" placeholder="Search plans…" autocomplete="off"
                                @disabled(! $panel_id) />
                            <x-icon name="search" class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-zinc-400" />
                        </div>
                        @if ($showPlanDropdown && $panel_id)
                            <div class="absolute z-20 mt-1.5 w-full overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-lg dark:border-zinc-700 dark:bg-zinc-800">
                                <div class="max-h-56 overflow-auto">
                                    @forelse ($this->plans as $plan)
                                        <button type="button" wire:click="selectPlan({{ $plan->id }})"
                                            class="flex w-full items-center justify-between gap-2 px-4 py-2.5 text-left hover:bg-zinc-50 dark:hover:bg-zinc-700/60">
                                            <span class="min-w-0">
                                                <span class="block truncate text-sm font-medium text-zinc-800 dark:text-zinc-200">{{ $plan->name }}</span>
                                                <span class="block truncate text-xs text-zinc-400">{{ $plan->billing_cycle->label() }} · {{ number_format($plan->price, 2) }} {{ auth()->user()->defaultCurrency() }}</span>
                                            </span>
                                        </button>
                                    @empty
                                        <div class="px-4 py-3 text-sm text-zinc-500 dark:text-zinc-400">No plans match.</div>
                                    @endforelse
                                </div>
                                <div class="border-t border-zinc-100 bg-zinc-50/60 px-4 py-2 dark:border-zinc-700 dark:bg-zinc-900/40">
                                    <button type="button" wire:click="openPlanQuickCreate"
                                        class="text-xs font-semibold text-primary-600 hover:text-primary-500">
                                        <x-icon name="plus" class="mr-1 inline h-3.5 w-3.5" />
                                        Add a new plan
                                    </button>
                                </div>
                            </div>
                        @endif
                        @if ($hosting_plan_id)
                            <div class="mt-1.5 flex items-center gap-2">
                                <span class="badge bg-primary-100 text-primary-700 dark:bg-primary-500/15 dark:text-primary-300">{{ $planSearch }}</span>
                                <button type="button" wire:click="clearPlan" class="text-xs text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-300">Clear</button>
                            </div>
                        @endif
                        <x-input-error :messages="$errors->get('hosting_plan_id')" class="mt-1" />
                    </div>
                @endif

                {{-- Dates --}}
                <div>
                    <x-input-label for="created_date" value="Start date" required />
                    <x-text-input id="created_date" type="date" wire:model.live="created_date" class="mt-1.5 w-full" />
                    <x-input-error :messages="$errors->get('created_date')" class="mt-1" />
                </div>
                <div>
                    <x-input-label for="expiry_date" value="Expiry date" required />
                    <x-text-input id="expiry_date" type="date" wire:model="expiry_date" class="mt-1.5 w-full" />
                    <p class="mt-1 text-xs text-zinc-400 dark:text-zinc-500">Fills automatically from the plan's billing cycle.</p>
                    <x-input-error :messages="$errors->get('expiry_date')" class="mt-1" />
                </div>

                {{-- Pricing --}}
                <div>
                    <x-input-label for="company_price" value="Provider cost" required />
                    <div class="relative mt-1.5">
                        <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-sm text-zinc-400">{{ $currency }}</span>
                        <x-text-input id="company_price" type="number" step="0.01" min="0" wire:model="company_price" class="w-full !pl-12" placeholder="0.00" />
                    </div>
                    <x-input-error :messages="$errors->get('company_price')" class="mt-1" />
                </div>
                <div>
                    <x-input-label for="client_price" value="Client price" required />
                    <div class="relative mt-1.5">
                        <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-sm text-zinc-400">{{ $currency }}</span>
                        <x-text-input id="client_price" type="number" step="0.01" min="0" wire:model="client_price" class="w-full !pl-12" placeholder="0.00" />
                    </div>
                    <x-input-error :messages="$errors->get('client_price')" class="mt-1" />
                </div>

                {{-- Currency --}}
                <div>
                    <x-input-label for="currency" value="Currency" required />
                    <x-text-input id="currency" wire:model="currency" class="mt-1.5 w-full" maxlength="3" placeholder="USD" />
                    <x-input-error :messages="$errors->get('currency')" class="mt-1" />
                </div>

                {{-- Auto renew --}}
                <div class="flex items-end">
                    <label class="flex cursor-pointer items-center gap-3 pb-1.5">
                        <input type="checkbox" wire:model="auto_renew_tracking" class="checkbox">
                        <span>
                            <span class="block text-sm font-medium text-zinc-800 dark:text-zinc-200">Track auto-renew</span>
                            <span class="block text-xs text-zinc-400 dark:text-zinc-500">Include this service in expiry reminders.</span>
                        </span>
                    </label>
                </div>
            </div>

            <div>
                <x-input-label for="notes" value="Notes" />
                <textarea id="notes" wire:model="notes" rows="3" class="input mt-1.5"
                    placeholder="Anything worth remembering about this service…"></textarea>
                <x-input-error :messages="$errors->get('notes')" class="mt-1" />
            </div>

            <div class="flex items-center justify-end gap-2 border-t border-zinc-100 pt-5 dark:border-zinc-800">
                <a href="{{ $service ? route('services.show', $service) : route('services.index') }}" wire:navigate class="btn-secondary">Cancel</a>
                <button type="submit" class="btn-primary">
                    <x-icon name="check-circle" class="h-4 w-4" />
                    {{ $service ? 'Save changes' : 'Create service' }}
                </button>
            </div>
        </div>
    </form>

    {{-- Client quick-create --}}
    <x-modal wire:model="showClientQuickCreate" title="Add a new client">
        <div class="space-y-4">
            <div>
                <x-input-label for="quickClientName" value="Client name" required />
                <x-text-input id="quickClientName" wire:model="quickClientName" class="mt-1.5 w-full" />
                <x-input-error :messages="$errors->get('quickClientName')" class="mt-1" />
            </div>
            <div>
                <x-input-label for="quickClientEmail" value="Email" />
                <x-text-input id="quickClientEmail" type="email" wire:model="quickClientEmail" class="mt-1.5 w-full" />
                <x-input-error :messages="$errors->get('quickClientEmail')" class="mt-1" />
            </div>
        </div>
        <div class="mt-6 flex justify-end gap-2">
            <button type="button" wire:click="$set('showClientQuickCreate', false)" class="btn-secondary">Cancel</button>
            <button type="button" wire:click="saveQuickClient" class="btn-primary">
                <x-icon name="check-circle" class="h-4 w-4" />
                Add client
            </button>
        </div>
    </x-modal>

    {{-- Panel quick-create --}}
    <x-modal wire:model="showPanelQuickCreate" title="Add a new panel">
        <div class="space-y-4">
            <div>
                <x-input-label for="quickPanelName" value="Panel name" required />
                <x-text-input id="quickPanelName" wire:model="quickPanelName" class="mt-1.5 w-full" placeholder="Shared cPanel #2" />
                <x-input-error :messages="$errors->get('quickPanelName')" class="mt-1" />
            </div>
            <div>
                <x-input-label for="quickPanelType" value="Type" required />
                <select id="quickPanelType" wire:model="quickPanelType" class="input mt-1.5">
                    @foreach ($panelTypes as $panelType)
                        <option value="{{ $panelType->value }}">{{ $panelType->label() }}</option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('quickPanelType')" class="mt-1" />
            </div>
            <div>
                <x-input-label for="quickPanelHost" value="Host" />
                <x-text-input id="quickPanelHost" wire:model="quickPanelHost" class="mt-1.5 w-full" placeholder="server2.example.com" />
                <x-input-error :messages="$errors->get('quickPanelHost')" class="mt-1" />
            </div>
        </div>
        <div class="mt-6 flex justify-end gap-2">
            <button type="button" wire:click="$set('showPanelQuickCreate', false)" class="btn-secondary">Cancel</button>
            <button type="button" wire:click="saveQuickPanel" class="btn-primary">
                <x-icon name="check-circle" class="h-4 w-4" />
                Add panel
            </button>
        </div>
    </x-modal>

    {{-- Plan quick-create --}}
    <x-modal wire:model="showPlanQuickCreate" title="Add a new plan">
        <div class="space-y-4">
            <div>
                <x-input-label for="quickPlanName" value="Plan name" required />
                <x-text-input id="quickPlanName" wire:model="quickPlanName" class="mt-1.5 w-full" placeholder="Business 20GB" />
                <x-input-error :messages="$errors->get('quickPlanName')" class="mt-1" />
            </div>
            <div>
                <x-input-label for="quickPlanCycle" value="Billing cycle" required />
                <select id="quickPlanCycle" wire:model="quickPlanCycle" class="input mt-1.5">
                    @foreach ($cycles as $cycle)
                        <option value="{{ $cycle->value }}">{{ $cycle->label() }}</option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('quickPlanCycle')" class="mt-1" />
            </div>
            <div>
                <x-input-label for="quickPlanPrice" value="Provider price" required />
                <div class="relative mt-1.5">
                    <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-sm text-zinc-400">{{ $currency }}</span>
                    <x-text-input id="quickPlanPrice" type="number" step="0.01" min="0" wire:model="quickPlanPrice" class="w-full !pl-12" placeholder="0.00" />
                </div>
                <x-input-error :messages="$errors->get('quickPlanPrice')" class="mt-1" />
            </div>
        </div>
        <div class="mt-6 flex justify-end gap-2">
            <button type="button" wire:click="$set('showPlanQuickCreate', false)" class="btn-secondary">Cancel</button>
            <button type="button" wire:click="saveQuickPlan" class="btn-primary">
                <x-icon name="check-circle" class="h-4 w-4" />
                Add plan
            </button>
        </div>
    </x-modal>
</div>