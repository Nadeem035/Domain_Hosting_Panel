<?php

namespace App\Services;

use App\Enums\ReminderTier;
use App\Enums\ServiceStatus;
use App\Models\Service;
use App\Notifications\RenewalReminder;
use Illuminate\Support\Facades\Log;

final class ReminderService
{
    /**
     * Scan every tracked service and send reminders where the tier changed,
     * the service just entered the reminder window, or a week has passed
     * since the last nudge.
     *
     * Returns the number of reminders sent.
     */
    public function checkAll(): int
    {
        $sent = 0;

        Service::query()
            ->where('status', '!=', ServiceStatus::Cancelled->value)
            ->where('auto_renew_tracking', true)
            ->whereNotNull('expiry_date')
            ->with(['user', 'client:id,name', 'hostingPlan:id,name'])
            ->chunkById(200, function ($services) use (&$sent) {
                foreach ($services as $service) {
                    if ($this->checkService($service)) {
                        $sent++;
                    }
                }
            });

        return $sent;
    }

    /**
     * Evaluate a single service. Returns true when a reminder was sent.
     */
    public function checkService(Service $service): bool
    {
        $tier = ReminderTierCalculator::tierFor($service->expiry_date);

        // Outside the 30-day window: clear stale tracking markers.
        if ($tier === null) {
            if ($service->last_expiry_tier !== null || $service->last_notified_at !== null) {
                $service->update([
                    'last_expiry_tier' => null,
                    'last_notified_at' => null,
                ]);
            }

            return false;
        }

        // An active service that slipped past its expiry is now expired.
        if ($tier === ReminderTier::Expired && $service->status === ServiceStatus::Active) {
            $service->update(['status' => ServiceStatus::Expired]);
        }

        $user = $service->user;

        if (! $user) {
            return false;
        }

        $shouldNotify = $service->last_notified_at === null
            || $service->last_expiry_tier !== $tier->value
            || $service->last_notified_at->lte(now()->subDays(7));

        if (! $shouldNotify) {
            return false;
        }

        $domain = $service->domain_name
            ?: $service->hostingPlan?->name
            ?: 'Service #'.$service->id;

        $daysLeft = ReminderTierCalculator::daysLeft($service->expiry_date);
        $urgency = $daysLeft >= 0 ? "expires in {$daysLeft} days" : 'expired '.abs($daysLeft).' days ago';

        $user->notify(new RenewalReminder([
            'service_id' => $service->id,
            'domain' => $domain,
            'client' => $service->client?->name,
            'tier' => $tier->value,
            'expiry_date' => $service->expiry_date->toDateString(),
            'days_left' => $daysLeft,
            'client_price' => (float) $service->client_price,
            'currency' => $service->currency ?: 'USD',
            'url' => route('services.show', $service),
            'title' => "{$tier->label()} renewal: {$domain}",
            'message' => "{$domain} {$urgency} ({$service->expiry_date->format('M j, Y')}).",
        ]));

        $service->update([
            'last_expiry_tier' => $tier->value,
            'last_notified_at' => now(),
        ]);

        Log::info('Renewal reminder sent', [
            'service_id' => $service->id,
            'user_id' => $user->id,
            'tier' => $tier->value,
        ]);

        return true;
    }
}