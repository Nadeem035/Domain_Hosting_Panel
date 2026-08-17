<?php

namespace App\Services;

use App\Enums\ServiceStatus;
use App\Models\Service;
use App\Models\ServiceRenewal;
use Illuminate\Support\Carbon;

final class ServiceRenewalService
{
    /**
     * Renew a service: record the renewal, extend the expiry date by the
     * plan's billing cycle (default 1 month) and reactivate the service.
     *
     * @param  array{payment_received?: bool, invoice_number?: string|null, notes?: string|null}  $data
     */
    public static function renew(Service $service, array $data = []): ServiceRenewal
    {
        $months = $service->hostingPlan?->billing_cycle?->months() ?? 1;
        $previousExpiry = $service->expiry_date?->copy() ?? Carbon::today();
        $newExpiry = $previousExpiry->copy()->addMonths($months);
        $paymentReceived = (bool) ($data['payment_received'] ?? true);

        $renewal = ServiceRenewal::create([
            'service_id' => $service->id,
            'renewed_on' => Carbon::today()->toDateString(),
            'previous_expiry_date' => $previousExpiry->toDateString(),
            'new_expiry_date' => $newExpiry->toDateString(),
            'company_price' => $service->company_price,
            'client_price' => $service->client_price,
            'payment_received' => $paymentReceived,
            'payment_received_date' => $paymentReceived ? Carbon::today()->toDateString() : null,
            'invoice_number' => $data['invoice_number'] ?? null,
            'notes' => $data['notes'] ?? null,
        ]);

        $service->update([
            'expiry_date' => $newExpiry->toDateString(),
            'status' => ServiceStatus::Active,
            'last_expiry_tier' => null,
            'last_payment_tier' => null,
            'last_notified_at' => null,
        ]);

        return $renewal;
    }
}