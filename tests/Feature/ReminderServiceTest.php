<?php

namespace Tests\Feature;

use App\Enums\ReminderTier;
use App\Models\Client;
use App\Models\Service;
use App\Models\User;
use App\Notifications\RenewalReminder;
use App\Services\ReminderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ReminderServiceTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    private function service(array $attributes = []): Service
    {
        $client = Client::factory()->for($this->user)->create();

        return Service::factory()->for($this->user)->for($client)->create($attributes);
    }

    public function test_service_entering_window_gets_first_reminder(): void
    {
        Notification::fake();

        $service = $this->service(['expiry_date' => now()->addDays(10)->toDateString()]);

        $sent = (new ReminderService())->checkAll();

        $this->assertSame(1, $sent);
        Notification::assertSentTo($this->user, RenewalReminder::class);

        $service->refresh();
        $this->assertSame(ReminderTier::DueSoon->value, $service->last_expiry_tier);
        $this->assertNotNull($service->last_notified_at);
    }

    public function test_tier_change_triggers_new_reminder(): void
    {
        Notification::fake();

        $service = $this->service([
            'expiry_date' => now()->addDays(10)->toDateString(),
            'last_expiry_tier' => ReminderTier::Upcoming->value,
            'last_notified_at' => now()->subDays(2),
        ]);

        (new ReminderService())->checkAll();

        Notification::assertSentTo($this->user, RenewalReminder::class);
        $this->assertSame(ReminderTier::DueSoon->value, $service->refresh()->last_expiry_tier);
    }

    public function test_no_reminder_when_nothing_changed(): void
    {
        Notification::fake();

        $this->service([
            'expiry_date' => now()->addDays(10)->toDateString(),
            'last_expiry_tier' => ReminderTier::DueSoon->value,
            'last_notified_at' => now()->subDays(2),
        ]);

        $sent = (new ReminderService())->checkAll();

        $this->assertSame(0, $sent);
        Notification::assertNothingSent();
    }

    public function test_weekly_renudge_after_seven_days(): void
    {
        Notification::fake();

        $this->service([
            'expiry_date' => now()->addDays(10)->toDateString(),
            'last_expiry_tier' => ReminderTier::DueSoon->value,
            'last_notified_at' => now()->subDays(8),
        ]);

        $sent = (new ReminderService())->checkAll();

        $this->assertSame(1, $sent);
        Notification::assertSentTo($this->user, RenewalReminder::class);
    }

    public function test_stale_markers_are_cleared_outside_the_window(): void
    {
        Notification::fake();

        $service = $this->service([
            'expiry_date' => now()->addDays(45)->toDateString(),
            'last_expiry_tier' => ReminderTier::DueSoon->value,
            'last_notified_at' => now()->subDays(1),
        ]);

        $sent = (new ReminderService())->checkAll();

        $this->assertSame(0, $sent);
        Notification::assertNothingSent();
        $this->assertNull($service->refresh()->last_expiry_tier);
        $this->assertNull($service->refresh()->last_notified_at);
    }

    public function test_active_service_past_expiry_flips_to_expired_and_reminds(): void
    {
        Notification::fake();

        $service = $this->service([
            'status' => 'active',
            'expiry_date' => now()->subDays(2)->toDateString(),
        ]);

        $sent = (new ReminderService())->checkAll();

        $this->assertSame(1, $sent);
        $this->assertSame('expired', $service->refresh()->status->value);
        Notification::assertSentTo($this->user, RenewalReminder::class);
    }

    public function test_skips_cancelled_and_untracked_services(): void
    {
        Notification::fake();

        $this->service(['status' => 'cancelled', 'expiry_date' => now()->addDays(3)->toDateString()]);
        $this->service(['auto_renew_tracking' => false, 'expiry_date' => now()->addDays(3)->toDateString()]);

        $sent = (new ReminderService())->checkAll();

        $this->assertSame(0, $sent);
        Notification::assertNothingSent();
    }

    public function test_reminder_targets_the_services_owner(): void
    {
        Notification::fake();

        $otherUser = User::factory()->create();
        $this->actingAs($otherUser);
        $otherClient = Client::factory()->for($otherUser)->create();
        $otherService = Service::factory()->for($otherUser)->for($otherClient)->create([
            'expiry_date' => now()->addDays(3)->toDateString(),
        ]);
        $this->actingAs($this->user);

        $sent = (new ReminderService())->checkService($otherService);

        $this->assertTrue($sent);
        Notification::assertSentTo($otherUser, RenewalReminder::class);
        Notification::assertNotSentTo($this->user, RenewalReminder::class);
    }

    public function test_via_returns_mail_only_when_email_enabled_and_tier_opted_in(): void
    {
        $data = $this->notificationData();

        $this->user->update(['notification_preferences' => [
            'email_enabled' => true,
            'email' => ['urgent' => true],
        ]]);
        $this->assertSame(['database', 'mail'], (new RenewalReminder($data))->via($this->user));

        $this->user->update(['notification_preferences' => [
            'email_enabled' => true,
            'email' => ['urgent' => false],
        ]]);
        $this->assertSame(['database'], (new RenewalReminder($data))->via($this->user));

        $this->user->update(['notification_preferences' => [
            'email_enabled' => false,
            'email' => ['urgent' => true],
        ]]);
        $this->assertSame(['database'], (new RenewalReminder($data))->via($this->user));
    }

    /**
     * @return array<string, mixed>
     */
    private function notificationData(): array
    {
        return [
            'service_id' => 1,
            'domain' => 'example.com',
            'client' => 'Acme',
            'tier' => 'urgent',
            'expiry_date' => now()->addDays(3)->toDateString(),
            'days_left' => 3,
            'client_price' => 25.0,
            'currency' => 'USD',
            'url' => route('services.show', 1),
            'title' => 'Urgent renewal: example.com',
            'message' => 'example.com expires in 3 days.',
        ];
    }
}