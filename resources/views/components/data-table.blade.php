@props([
    'columns' => [],
    'sortBy' => null,
    'sortDir' => null,
    'sticky' => true,
])

<thead>
    <tr class="border-b border-zinc-200 bg-zinc-50/80 dark:border-zinc-700/60 dark:bg-zinc-800/40">
        @foreach ($columns as $column)
            @php
                $sortable = ($column['sortable'] ?? false) && $sortBy !== null;
                $isActive = $sortable && $sortBy === $column['key'];
            @endphp
            <th scope="col"
                class="whitespace-nowrap px-5 py-3 text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400 {{ $column['class'] ?? '' }}">
                @if ($sortable)
                    <button type="button" wire:click="sortBy('{{ $column['key'] }}')"
                        class="group inline-flex items-center gap-1.5 transition hover:text-zinc-900 dark:hover:text-zinc-100 {{ $isActive ? 'text-zinc-900 dark:text-zinc-100' : '' }}">
                        <span>{{ $column['label'] }}</span>
                        <span class="relative flex h-3 w-3 flex-col items-center justify-center {{ $isActive ? '' : 'opacity-40 group-hover:opacity-100' }}">
                            <x-icon name="chevron-up" class="h-2 w-2 transition {{ $isActive && $sortDir === 'asc' ? 'text-primary-600 dark:text-primary-400' : '' }}" />
                            <x-icon name="chevron-down" class="-mt-0.5 h-2 w-2 transition {{ $isActive && $sortDir === 'desc' ? 'text-primary-600 dark:text-primary-400' : '' }}" />
                        </span>
                    </button>
                @else
                    <span class="inline-flex items-center gap-1.5">{{ $column['label'] }}</span>
                @endif
            </th>
        @endforeach
    </tr>
</thead>
