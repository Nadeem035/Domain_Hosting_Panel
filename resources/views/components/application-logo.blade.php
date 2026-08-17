@props(['class' => 'h-9 w-9'])

<svg {{ $attributes->merge(['class' => $class]) }} viewBox="0 0 40 40" fill="none" aria-hidden="true">
    <rect width="40" height="40" rx="11" fill="url(#dh-brand-gradient)" />
    <rect width="40" height="40" rx="11" stroke="white" stroke-opacity="0.12" stroke-width="1.5" />
    <path d="M13 12.5v15M27 12.5v15M13 20h14" stroke="white" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" opacity="0.95" />
    <circle cx="29.5" cy="10.5" r="3.25" fill="#4f46d8" stroke="white" stroke-width="1.6" />
    <defs>
        <linearGradient id="dh-brand-gradient" x1="0" y1="0" x2="40" y2="40" gradientUnits="userSpaceOnUse">
            <stop stop-color="#6366e3" />
            <stop offset="1" stop-color="#4339be" />
        </linearGradient>
    </defs>
</svg>