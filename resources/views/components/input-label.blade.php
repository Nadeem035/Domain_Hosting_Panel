@props(['value', 'required' => false])

<label {{ $attributes->merge(['class' => 'block text-sm font-medium text-zinc-700 dark:text-zinc-300']) }}>
    {{ $value ?? $slot }}
    @if ($required)
        <span class="text-rose-500">*</span>
    @endif
</label>