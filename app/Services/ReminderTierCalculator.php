<?php

namespace App\Services;

use App\Enums\ReminderTier;
use Carbon\Carbon;

final class ReminderTierCalculator
{
    /**
     * Compute the urgency tier for a date relative to today.
     *
     * Returns null when the date is more than 30 days away (no urgency).
     *
     * @see spec section 5
     */
    public static function tierFor(Carbon $date, Carbon $today = null): ?ReminderTier
    {
        $days = self::daysLeft($date, $today);

        return match (true) {
            $days <= 0 => ReminderTier::Expired,
            $days <= 7 => ReminderTier::Urgent,
            $days <= 15 => ReminderTier::DueSoon,
            $days <= 30 => ReminderTier::Upcoming,
            default => null,
        };
    }

    /**
     * Whole days from today (in the given timezone) until the date. Negative when past.
     */
    public static function daysLeft(Carbon $date, Carbon $today = null): int
    {
        $today = ($today ?? now())->startOfDay();

        return $today->diffInDays($date->copy()->startOfDay(), false);
    }
}