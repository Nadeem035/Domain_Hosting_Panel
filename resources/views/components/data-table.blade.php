@props([
    'columns' => [],
    'sortBy' => null,
    'sortDir' => null,
])

<thead>
    <tr class="border-b border-zinc-200 dark:border-zinc-700/60">
        @foreach ($columns as $column)
            <th scope="col" class="whitespace-nowrap px-5 py-3 text-xs font-semibold uppercase tracking-wider text-zinc-400 dark:text-zinc-500 {{ $column['class'] ?? '' }}">
                @if (($column['sortable'] ?? false) && $sortBy !== null)
                    <button type="button" wire:click="sortBy('{{ $column['key'] }}')"
                        class="inline-flex items-center gap-1 transition hover:text-zinc-600 dark:hover:text-zinc-300">
                        {{ $column['label'] }}
                        @if ($sortBy === $column['key'])
                            <x-icon name="{{ $sortDir === 'asc' ? 'chevron-up' : 'chevron-down' }}" class="h-3 w-3" />
                        @endif
                    </button>
                @else
                    {{ $column['label'] }}
                @endif
            </th>
        @endforeach
    </tr>
</thead>