@props([
    'columns' => [],
    'sortBy' => null,
    'sortDir' => null,
    'rows' => null,
    'countLabel' => 'results',
    'meta' => true,
])

@php
    $paginator = $rows instanceof \Illuminate\Contracts\Pagination\Paginator ? $rows : null;
    $hasPages = $paginator instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator && $paginator->hasPages();
@endphp

<div class="card overflow-hidden">
    @if ($meta && $paginator instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator)
        <div
            class="flex flex-wrap items-center justify-between gap-2 border-b border-zinc-100 px-5 py-3 dark:border-zinc-800">
            <p class="text-sm text-zinc-500 dark:text-zinc-400">
                <span class="font-semibold text-zinc-900 dark:text-zinc-100">{{ $paginator->total() }}</span>
                {{ $paginator->total() === 1 ? $countLabel : \Illuminate\Support\Str::plural($countLabel) }}
            </p>
            <p class="text-xs text-zinc-400 dark:text-zinc-500">
                Page {{ $paginator->currentPage() }} of {{ $paginator->lastPage() }}
            </p>
        </div>
    @endif

    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead>
                <tr class="border-b border-zinc-200 bg-zinc-100/70 dark:border-zinc-800 dark:bg-zinc-800/50">
                    @foreach ($columns as $column)
                        @php
                            $sortable = ($column['sortable'] ?? false) && $sortBy !== null;
                            $isActive = $sortable && $sortBy === $column['key'];
                            $right = str_contains($column['class'] ?? '', 'text-right');
                        @endphp
                        <th scope="col"
                            aria-sort="{{ $sortable ? ($isActive ? ($sortDir === 'asc' ? 'ascending' : 'descending') : 'none') : null }}"
                            class="whitespace-nowrap select-none px-5 py-3 text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400 {{ $column['class'] ?? '' }}">
                            @if ($sortable)
                                <button type="button" wire:click="sortBy('{{ $column['key'] }}')"
                                    class="group inline-flex w-full items-center gap-1.5 transition-colors {{ $right ? 'justify-end' : '' }} {{ $isActive ? 'text-primary-700 dark:text-primary-300' : 'hover:text-zinc-900 dark:hover:text-zinc-100' }}">
                                    <span>{{ $column['label'] }}</span>
                                    <span
                                        class="relative flex h-3 w-3 flex-col items-center justify-center {{ $isActive ? '' : 'opacity-30 group-hover:opacity-100' }}">
                                        <x-icon name="chevron-up"
                                            class="h-2 w-2 {{ $isActive && $sortDir === 'asc' ? 'text-primary-600 dark:text-primary-400' : '' }}" />
                                        <x-icon name="chevron-down"
                                            class="-mt-0.5 h-2 w-2 {{ $isActive && $sortDir === 'desc' ? 'text-primary-600 dark:text-primary-400' : '' }}" />
                                    </span>
                                </button>
                            @else
                                <span
                                    class="inline-flex w-full items-center gap-1.5 {{ $right ? 'justify-end' : '' }}">{{ $column['label'] }}</span>
                            @endif
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                {{ $slot }}
            </tbody>
        </table>
    </div>

    @if ($hasPages)
        <div class="border-t border-zinc-200 px-5 py-3 dark:border-zinc-700/60">{{ $paginator->links() }}</div>
    @endif
</div>