<?php

namespace App\Console\Commands;

use App\Services\ReminderService;
use Illuminate\Console\Command;

class CheckRenewalReminders extends Command
{
    protected $signature = 'services:check-reminders';

    protected $description = 'Scan tracked services and send renewal reminders (in-app and email)';

    public function handle(ReminderService $reminders): int
    {
        $sent = $reminders->checkAll();

        $this->info("Reminder check complete — {$sent} reminder(s) sent.");

        return self::SUCCESS;
    }
}