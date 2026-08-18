<div>
    <x-page-heading title="Activity log" subtitle="Full activity history with tenant isolation." />

    {{-- Search --}}
    <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:flex-wrap">
        <div class="relative w-full sm:max-w-xs sm:flex-1">
            <x-icon name="search" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-zinc-400" />
            <input type="search" wire:model.live.debounce.300ms="search" placeholder="Search by description…"
                class="input !pl-9">
        </div>
    </div>

    {{-- Table --}}
    <div class="mt-4">
        <div wire:loading.delay.long class="space-y-3">
            @for ($i = 0; $i < 4; $i++)
                <div class="card flex items-center gap-4 p-4">
                    <div class="skeleton h-10 w-10 rounded-full"></div>
                    <div class="flex-1 space-y-2">
                        <div class="skeleton h-4 w-40"></div>
                        <div class="skeleton h-3 w-64"></div>
                    </div>
                </div>
            @endfor
        </div>

        <div wire:loading.remove wire:target="search, goToPage, previousPage, nextPage">
            <div class="card overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <x-data-table :columns="[
                            ['label' => 'Time'],
                            ['label' => 'Actor'],
                            ['label' => 'Event'],
                            ['label' => 'Subject'],
                            ['label' => 'Details'],
                        ]" />
                        <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                            @forelse ($activities as $activity)
                                <tr class="transition hover:bg-zinc-50/80 dark:hover:bg-zinc-800/40">
                                    <td class="whitespace-nowrap px-5 py-4 text-zinc-600 dark:text-zinc-300">
                                        {{ $activity->created_at->diffForHumans() }}
                                    </td>
                                    <td class="whitespace-nowrap px-5 py-4 text-zinc-600 dark:text-zinc-300">
                                        {{ $activity->causer?->name ?? 'System' }}
                                    </td>
                                    <td class="whitespace-nowrap px-5 py-4">
                                        <span class="badge {{ match ($activity->event) {
                                            'created' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-400',
                                            'updated' => 'bg-amber-100 text-amber-700 dark:bg-amber-500/15 dark:text-amber-400',
                                            default => 'bg-rose-100 text-rose-700 dark:bg-rose-500/15 dark:text-rose-400',
                                        } }}">
                                            {{ $activity->event }}
                                        </span>
                                    </td>
                                    <td class="whitespace-nowrap px-5 py-4">
                                        @php($sf = $this->subjectFor($activity))
                                        @if ($sf['url'])
                                            <a href="{{ $sf['url'] }}" wire:navigate class="text-primary-600 hover:text-primary-500 dark:text-primary-400 dark:hover:text-primary-300">{{ $sf['label'] }}</a>
                                        @else
                                            <span class="text-zinc-400 dark:text-zinc-500">{{ $sf['label'] }}</span>
                                        @endif
                                    </td>
                                    <td class="max-w-[320px] px-5 py-4 text-zinc-600 dark:text-zinc-300">
                                        @if ($activity->event === 'updated' && ! empty($fields = $this->changedFields($activity)))
                                            <span class="block truncate" title="{{ implode(', ', $fields) }}">{{ implode(', ', $fields) }}</span>
                                        @else
                                            <span class="block truncate">{{ $activity->description }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5">
                                        @if ($this->search !== '')
                                            <x-empty-state icon="search" title="No activities match your search">
                                                <button wire:click="$set('search', '')" class="btn-ghost text-primary-600 dark:text-primary-400">
                                                    Clear search
                                                </button>
                                            </x-empty-state>
                                        @else
                                            <x-empty-state icon="check-circle" title="No activities yet"
                                                text="Activities appear here when users or admins perform actions in the system." />
                                        @endif
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($activities->hasPages())
                    <div class="border-t border-zinc-200 px-5 py-3 dark:border-zinc-700/60">{{ $activities->links() }}</div>
                @endif
            </div>
        </div>
    </div>
</div>