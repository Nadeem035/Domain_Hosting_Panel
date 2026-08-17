<div>
    <x-page-heading :title="$panel->name"
        :subtitle="$panel->type->label() . ' panel' . ($panel->is_active ? '' : ' — inactive') . ', created ' . $panel->created_at?->format('M Y')">
        <x-slot:actions>
            <a href="{{ route('panels.edit', $panel) }}" wire:navigate class="btn-secondary">
                <x-icon name="pencil-square" class="h-4 w-4" />
                Edit
            </a>
        </x-slot:actions>
    </x-page-heading>

    <div class="mt-6 grid gap-4 lg:grid-cols-[1fr_2fr]">
        {{-- Panel details --}}
        <div class="card space-y-4 p-6">
            <div class="flex items-center gap-4">
                <span class="flex h-14 w-14 items-center justify-center rounded-2xl bg-primary-100 text-xl font-bold text-primary-700 dark:bg-primary-500/15 dark:text-primary-300">
                    {{ strtoupper(substr($panel->name, 0, 1)) }}
                </span>
                <div class="space-y-1">
                    <span class="badge bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">{{ $panel->type->label() }}</span>
                    <span class="badge block {{ $panel->is_active ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-400' : 'bg-rose-100 text-rose-700 dark:bg-rose-500/15 dark:text-rose-400' }}">
                        {{ $panel->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </div>
            </div>
            <dl class="space-y-3 text-sm">
                @if ($panel->host)
                    <div class="flex items-start gap-3">
                        <x-icon name="server" class="mt-0.5 h-4 w-4 shrink-0 text-zinc-400" />
                        <div>
                            <dt class="text-xs font-medium uppercase tracking-wide text-zinc-400">Host</dt>
                            <dd class="font-medium text-zinc-800 dark:text-zinc-200">{{ $panel->host }}</dd>
                        </div>
                    </div>
                @endif
                @if ($panel->ip_address)
                    <div class="flex items-start gap-3">
                        <x-icon name="globe" class="mt-0.5 h-4 w-4 shrink-0 text-zinc-400" />
                        <div>
                            <dt class="text-xs font-medium uppercase tracking-wide text-zinc-400">IP address</dt>
                            <dd class="font-medium text-zinc-800 dark:text-zinc-200">{{ $panel->ip_address }}</dd>
                        </div>
                    </div>
                @endif
                @if ($panel->username)
                    <div class="flex items-start gap-3">
                        <x-icon name="user-circle" class="mt-0.5 h-4 w-4 shrink-0 text-zinc-400" />
                        <div>
                            <dt class="text-xs font-medium uppercase tracking-wide text-zinc-400">Username</dt>
                            <dd class="font-medium text-zinc-800 dark:text-zinc-200">{{ $panel->username }}</dd>
                        </div>
                    </div>
                @endif
                @if ($panel->login_url)
                    <div class="flex items-start gap-3">
                        <x-icon name="link" class="mt-0.5 h-4 w-4 shrink-0 text-zinc-400" />
                        <div>
                            <dt class="text-xs font-medium uppercase tracking-wide text-zinc-400">Login URL</dt>
                            <dd class="font-medium text-zinc-800 dark:text-zinc-200">
                                <a href="{{ $panel->login_url }}" target="_blank" rel="noopener" class="hover:text-primary-600">{{ $panel->login_url }}</a>
                            </dd>
                        </div>
                    </div>
                @endif
                <div class="flex items-start gap-3">
                    <x-icon name="users" class="mt-0.5 h-4 w-4 shrink-0 text-zinc-400" />
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-zinc-400">Client limit</dt>
                        <dd class="font-medium text-zinc-800 dark:text-zinc-200">{{ $panel->client_limit === 0 ? 'Unlimited' : number_format($panel->client_limit) }}</dd>
                    </div>
                </div>
            </dl>
            @if ($panel->notes)
                <div class="rounded-xl bg-zinc-50 p-4 text-sm text-zinc-600 dark:bg-zinc-800/60 dark:text-zinc-300">
                    <p class="text-xs font-semibold uppercase tracking-wide text-zinc-400 dark:text-zinc-500">Notes</p>
                    <p class="mt-1.5 whitespace-pre-wrap">{{ $panel->notes }}</p>
                </div>
            @endif
        </div>

        <div class="space-y-4">
            {{-- Stats --}}
            <div class="grid grid-cols-3 gap-4">
                <div class="card p-5">
                    <p class="text-xs font-medium text-zinc-400 dark:text-zinc-500">Plans</p>
                    <p class="mt-1.5 text-2xl font-bold text-zinc-900 dark:text-zinc-100">{{ $this->stats['plan_count'] }}</p>
                </div>
                <div class="card p-5">
                    <p class="text-xs font-medium text-zinc-400 dark:text-zinc-500">Active plans</p>
                    <p class="mt-1.5 text-2xl font-bold text-zinc-900 dark:text-zinc-100">{{ $this->stats['active_plan_count'] }}</p>
                </div>
                <div class="card p-5">
                    <p class="text-xs font-medium text-zinc-400 dark:text-zinc-500">Active services</p>
                    <p class="mt-1.5 text-2xl font-bold text-zinc-900 dark:text-zinc-100">{{ $this->stats['service_count'] }}</p>
                </div>
            </div>

            {{-- Plans --}}
            <div class="card overflow-hidden">
                <div class="flex items-center justify-between border-b border-zinc-100 px-5 py-4 dark:border-zinc-800">
                    <h2 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Hosting plans</h2>
                    <button wire:click="startAddPlan" class="btn-primary !px-3 !py-1.5 text-xs">
                        <x-icon name="plus" class="h-3.5 w-3.5" />
                        Add plan
                    </button>
                </div>

                @if ($showPlanForm)
                    <div class="border-b border-zinc-100 bg-zinc-50/60 p-5 dark:border-zinc-800 dark:bg-zinc-900/40">
                        <h3 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">
                            {{ $editingPlanId ? 'Edit plan' : 'New plan' }}
                        </h3>
                        <div class="mt-4 grid gap-4 sm:grid-cols-2">
                            <div>
                                <x-input-label for="planName" value="Plan name" required />
                                <x-text-input id="planName" wire:model="planName" class="mt-1.5 w-full" placeholder="Business 20GB" />
                                <x-input-error :messages="$errors->get('planName')" class="mt-1" />
                            </div>
                            <div>
                                <x-input-label for="planCycle" value="Billing cycle" required />
                                <select id="planCycle" wire:model="planCycle" class="input mt-1.5">
                                    @foreach ($cycles as $cycle)
                                        <option value="{{ $cycle->value }}">{{ $cycle->label() }}</option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('planCycle')" class="mt-1" />
                            </div>
                            <div>
                                <x-input-label for="planPrice" value="Provider price" required />
                                <div class="relative mt-1.5">
                                    <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-sm text-zinc-400">{{ auth()->user()->defaultCurrency() }}</span>
                                    <x-text-input id="planPrice" type="number" step="0.01" min="0" wire:model="planPrice" class="w-full !pl-12" placeholder="0.00" />
                                </div>
                                <x-input-error :messages="$errors->get('planPrice')" class="mt-1" />
                            </div>
                            <div>
                                <x-input-label for="planIsActive" value="Status" />
                                <select id="planIsActive" wire:model="planIsActive" class="input mt-1.5">
                                    <option value="1">Active</option>
                                    <option value="0">Inactive</option>
                                </select>
                                <x-input-error :messages="$errors->get('planIsActive')" class="mt-1" />
                            </div>
                            <div>
                                <x-input-label for="planDiskSpace" value="Disk space" />
                                <x-text-input id="planDiskSpace" wire:model="planDiskSpace" class="mt-1.5 w-full" placeholder="20 GB" />
                                <x-input-error :messages="$errors->get('planDiskSpace')" class="mt-1" />
                            </div>
                            <div>
                                <x-input-label for="planBandwidth" value="Bandwidth" />
                                <x-text-input id="planBandwidth" wire:model="planBandwidth" class="mt-1.5 w-full" placeholder="100 GB" />
                                <x-input-error :messages="$errors->get('planBandwidth')" class="mt-1" />
                            </div>
                            <div class="sm:col-span-2">
                                <x-input-label for="planDescription" value="Description" />
                                <x-text-input id="planDescription" wire:model="planDescription" class="mt-1.5 w-full" placeholder="Best value for growing sites" />
                                <x-input-error :messages="$errors->get('planDescription')" class="mt-1" />
                            </div>
                            <div class="sm:col-span-2">
                                <x-input-label for="planFeaturesText" value="Features" />
                                <x-text-input id="planFeaturesText" wire:model="planFeaturesText" class="mt-1.5 w-full" placeholder="SSL certificate, Daily backups, Free domain" />
                                <p class="mt-1 text-xs text-zinc-400 dark:text-zinc-500">Comma-separated list.</p>
                                <x-input-error :messages="$errors->get('planFeaturesText')" class="mt-1" />
                            </div>
                        </div>
                        <div class="mt-4 flex justify-end gap-2">
                            <button type="button" wire:click="cancelPlanForm" class="btn-secondary">Cancel</button>
                            <button type="button" wire:click="savePlan" class="btn-primary">
                                <x-icon name="check-circle" class="h-4 w-4" />
                                {{ $editingPlanId ? 'Save changes' : 'Add plan' }}
                            </button>
                        </div>
                    </div>
                @endif

                <div class="grid grid-cols-1 gap-x-6 gap-y-1 px-5 py-3 text-xs font-semibold uppercase tracking-wider text-zinc-400 dark:text-zinc-500 sm:grid-cols-[2fr_1fr_1fr_1.5fr_auto]">
                    <span>Plan</span>
                    <span>Cycle</span>
                    <span>Price</span>
                    <span>Services</span>
                    <span></span>
                </div>

                @forelse ($plans as $plan)
                    <div class="table-row grid grid-cols-1 gap-2 px-5 py-4 sm:grid-cols-[2fr_1fr_1fr_1.5fr_auto] sm:items-center">
                        <div class="min-w-0">
                            <span class="block truncate font-semibold text-zinc-900 dark:text-zinc-100">{{ $plan->name }}</span>
                            <span class="block truncate text-xs text-zinc-400 dark:text-zinc-500">
                                {{ $plan->disk_space ? $plan->disk_space.' · ' : '' }}{{ $plan->bandwidth ? $plan->bandwidth.' · ' : '' }}{{ collect($plan->features ?? [])->take(2)->implode(', ') ?: '—' }}
                            </span>
                        </div>
                        <div class="text-sm text-zinc-600 dark:text-zinc-300">{{ $plan->billing_cycle->label() }}</div>
                        <div class="text-sm font-semibold text-zinc-800 dark:text-zinc-200">{{ number_format($plan->price, 2) }} {{ auth()->user()->defaultCurrency() }}</div>
                        <div class="flex items-center gap-2">
                            <span class="badge bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">{{ $plan->services_count }}</span>
                            <span class="badge {{ $plan->is_active ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-400' : 'bg-rose-100 text-rose-700 dark:bg-rose-500/15 dark:text-rose-400' }}">
                                {{ $plan->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </div>
                        <div class="flex items-center gap-1">
                            <button wire:click="startEditPlan({{ $plan->id }})" class="btn-ghost !p-2" title="Edit plan">
                                <x-icon name="pencil-square" class="h-4 w-4" />
                            </button>
                            <button wire:click="confirmDeletePlan({{ $plan->id }})" class="btn-ghost !p-2 text-rose-500 hover:!bg-rose-50 hover:!text-rose-600 dark:hover:!bg-rose-500/10" title="Delete plan">
                                <x-icon name="trash" class="h-4 w-4" />
                            </button>
                        </div>
                    </div>
                @empty
                    <div class="px-6 py-12 text-center">
                        <x-icon name="circle-stack" class="mx-auto h-10 w-10 text-zinc-300 dark:text-zinc-600" />
                        <p class="mt-4 text-sm font-medium text-zinc-600 dark:text-zinc-300">No plans on this panel yet</p>
                        <p class="mt-1 text-xs text-zinc-400 dark:text-zinc-500">Add a plan to attach it to hosting services.</p>
                        <button wire:click="startAddPlan" class="btn-primary mt-4">
                            <x-icon name="plus" class="h-4 w-4" />
                            Add your first plan
                        </button>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Plan delete confirmation --}}
    <x-modal wire:model="deletingPlan" title="Delete plan">
        <p class="text-sm text-zinc-600 dark:text-zinc-300">
            Are you sure you want to delete <span class="font-semibold text-zinc-900 dark:text-zinc-100">{{ $deletingPlan?->name }}</span>?
            Plans attached to services cannot be deleted.
        </p>
        <div class="mt-6 flex justify-end gap-2">
            <button type="button" wire:click="$set('deletingPlan', null)" class="btn-secondary">Cancel</button>
            <button type="button" wire:click="deletePlan" class="btn-danger">
                <x-icon name="trash" class="h-4 w-4" />
                Delete plan
            </button>
        </div>
    </x-modal>
</div>