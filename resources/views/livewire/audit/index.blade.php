<div>
    <x-page-heading title="Activity log" subtitle="Full activity history with tenant isolation." />

    {{-- Search --}}
    <div class="mt-4 mb-4">
        <x-input-label for="search" value="Search description" />
        <x-text-input id="search" wire:model="search" class="input mt-1.5" placeholder="Search by description…" />
    </div>

    {{-- Table --}}
    @if ($activities->count() > 0)
        <div class="overflow-x-auto rounded-lg border border-zinc-200 dark:border-zinc-700">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-zinc-200 dark:border-zinc-700/60">
                        <th scope="col" class="whitespace-nowrap px-4 py-3 text-xs font-semibold uppercase tracking-wider text-zinc-400 dark:text-zinc-500">Time</th>
                        <th scope="col" class="whitespace-nowrap px-4 py-3 text-xs font-semibold uppercase tracking-wider text-zinc-400 dark:text-zinc-500">Actor</th>
                        <th scope="col" class="whitespace-nowrap px-4 py-3 text-xs font-semibold uppercase tracking-wider text-zinc-400 dark:text-zinc-500">Event</th>
                        <th scope="col" class="whitespace-nowrap px-4 py-3 text-xs font-semibold uppercase tracking-wider text-zinc-400 dark:text-zinc-500">Subject</th>
                        <th scope="col" class="whitespace-nowrap px-4 py-3 text-xs font-semibold uppercase tracking-wider text-zinc-400 dark:text-zinc-500">Details</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-zinc-200 dark:bg-zinc-950">
                    @forelse ($activities as $activity)
                        <tr class="transition hover:bg-zinc-50/80 dark:hover:bg-zinc-800/40">
                            <td class="whitespace-nowrap px-4 py-4 text-zinc-500 dark:text-zinc-400">
                                {{ $activity->created_at->diffForHumans() }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-4 text-zinc-500 dark:text-zinc-400">
                                {{ $activity->causer?->name ?? 'System' }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-4 text-zinc-500 dark:text-zinc-400 badge {{ $activity->event === 'created' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-400' : ($activity->event === 'updated' ? 'bg-amber-100 text-amber-700 dark:bg-amber-500/15 dark:text-amber-400' : 'bg-rose-100 text-rose-700 dark:bg-rose-500/15 dark:text-rose-400') }}}{$activity->event}</td>
                            <td class="whitespace-nowrap px-4 py-4">
                                @php
                                    $sf = $this->subjectFor($activity);
                                @endphp
                                @if ($sf['url'])
                                    <a href="{{ $sf['url'] }}" wire:navigate class="text-primary-600 dark:text-primary-400 hover:underline">{{ $sf['label'] }}</a>
                                @else
                                    <span class="text-zinc-400 dark:text-zinc-500">{{ $sf['label'] }}</span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-4 py-4 text-zinc-500 dark:text-zinc-400">
                                @if ($activity->event === 'updated')
                                    @php
                                        $fields = $this->changedFields($activity);
                                    @endphp
                                    @if (! empty($fields))
                                        {{ implode(', ', $fields) }}
                                    @else
                                        {{ $activity->description }}
                                    @endif
                                @else
                                    {{ $activity->description }}
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-10 text-zinc-400 dark:text-zinc-600">
                                <x-icon name="check-circle" class="mx-auto mb-3 h-8 w-8 text-emerald-500" />
                                <p class="text-sm font-medium text-zinc-600 dark:text-zinc-300">Nothing to display</p>
                                <p class="text-xs text-zinc-500 dark:text-zinc-400">No activities match your filters.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        {{ $activities->links() }}
    @else
        <div class="py-12 text-center text-zinc-500 dark:text-zinc-400">
            <x-icon name="check-circle" class="mx-auto mb-3 h-12 w-12 text-emerald-500" />
            <p class="text-sm font-medium text-zinc-600 dark:text-zinc-300">No activities yet</p>
            <p class="text-xs text-zinc-400 dark:text-zinc-400">Activities appear here when users or admins perform actions in the system.</p>
        </div>
    @endif
</div>