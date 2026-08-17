<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Services — {{ now()->format('Y-m-d') }}</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #27272a; margin: 0; }
        h1 { font-size: 18px; margin: 0 0 2px; }
        .meta { color: #71717a; margin-bottom: 16px; }
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; background: #f4f4f5; font-size: 10px; text-transform: uppercase; letter-spacing: 0.05em; color: #71717a; padding: 8px 6px; }
        td { padding: 7px 6px; border-bottom: 1px solid #e4e4e7; }
        .right { text-align: right; }
        .badge { display: inline-block; padding: 1px 6px; border-radius: 9999px; font-size: 9px; }
        .green { background: #dcfce7; color: #166534; }
        .red { background: #ffe4e6; color: #9f1239; }
        .amber { background: #fef3c7; color: #92400e; }
        .sky { background: #e0f2fe; color: #075985; }
        .zinc { background: #f4f4f5; color: #52525b; }
    </style>
</head>
<body>
    <h1>Services</h1>
    <p class="meta">Generated {{ now()->format('M j, Y H:i') }} · {{ count($services) }} services</p>

    <table>
        <thead>
            <tr>
                <th>Service</th>
                <th>Client</th>
                <th>Type</th>
                <th>Expiry</th>
                <th>Tier</th>
                <th class="right">Cost</th>
                <th class="right">Price</th>
                <th class="right">Profit</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($services as $service)
                @php($tier = \App\Services\ReminderTierCalculator::tierFor($service->expiry_date))
                <tr>
                    <td>{{ $service->domain_name ?: ($service->hostingPlan?->name ?: 'Service #'.$service->id) }}</td>
                    <td>{{ $service->client?->name }}</td>
                    <td>{{ $service->type->label() }}</td>
                    <td>{{ $service->expiry_date?->format('M j, Y') }}</td>
                    <td>
                        @if ($tier)
                            <span class="badge {{ match ($tier) {
                                $tier::Expired => 'red',
                                $tier::Urgent => 'red',
                                $tier::DueSoon => 'amber',
                                default => 'sky',
                            } }}">{{ $tier->label() }}</span>
                        @else
                            <span class="badge zinc">Later</span>
                        @endif
                    </td>
                    <td class="right">{{ number_format((float) $service->company_price, 2) }}</td>
                    <td class="right">{{ number_format((float) $service->client_price, 2) }}</td>
                    <td class="right">{{ number_format($service->profit(), 2) }}</td>
                    <td>
                        <span class="badge {{ $service->status->value === 'active' ? 'green' : ($service->status->value === 'expired' ? 'red' : 'zinc') }}">
                            {{ $service->status->label() }}
                        </span>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>