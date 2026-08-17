<div>
    <h1 class="text-2xl font-bold tracking-tight text-zinc-900 dark:text-zinc-100">Dashboard</h1>

    <div class="mt-8 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach (['Active services', 'Total clients', 'Monthly revenue', 'Upcoming renewals'] as $label)
            <div class="card p-5">
                <div class="skeleton h-4 w-24"></div>
                <div class="skeleton mt-3 h-8 w-16"></div>
            </div>
        @endforeach
    </div>
</div>