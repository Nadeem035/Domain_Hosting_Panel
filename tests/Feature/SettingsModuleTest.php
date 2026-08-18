<?php

namespace Tests\Feature;

use App\Livewire\Pages\Settings\Settings;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SettingsModuleTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'theme_preference' => 'dark',
            'timezone' => 'Europe/Berlin',
            'notification_preferences' => [
                'currency' => 'EUR',
                'email_enabled' => false,
                'email' => ['expired' => true, 'urgent' => false],
            ],
        ]);
        $this->actingAs($this->user);
    }

    public function test_settings_mounts_with_user_preferences(): void
    {
        Livewire::test(Settings::class)
            ->assertSet('theme', 'dark')
            ->assertSet('timezone', 'Europe/Berlin')
            ->assertSet('currency', 'EUR')
            ->assertSet('emailEnabled', false)
            ->assertSet('emailTiers.expired', true)
            ->assertSet('emailTiers.urgent', false);
    }

    public function test_settings_defaults_when_no_preferences_exist(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test(Settings::class)
            ->assertSet('theme', 'system')
            ->assertSet('currency', 'USD')
            ->assertSet('emailEnabled', true)
            ->assertSet('emailTiers.urgent', true);
    }

    public function test_settings_can_be_saved(): void
    {
        Livewire::test(Settings::class)
            ->set('theme', 'light')
            ->set('timezone', 'America/New_York')
            ->set('currency', 'GBP')
            ->set('emailEnabled', true)
            ->set('emailTiers.urgent', false)
            ->call('save')
            ->assertHasNoErrors()
            ->assertDispatched('toast');

        $this->user->refresh();

        $this->assertSame('light', $this->user->theme_preference);
        $this->assertSame('America/New_York', $this->user->timezone);
        $this->assertSame('GBP', $this->user->notification_preferences['currency']);
        $this->assertTrue($this->user->notification_preferences['email_enabled']);
        $this->assertFalse($this->user->notification_preferences['email']['urgent']);
        $this->assertTrue($this->user->notification_preferences['email']['expired']);
    }

    public function test_settings_rejects_invalid_currency(): void
    {
        Livewire::test(Settings::class)
            ->set('currency', 'US')
            ->call('save')
            ->assertHasErrors(['currency' => 'size']);
    }

    public function test_settings_rejects_invalid_theme(): void
    {
        Livewire::test(Settings::class)
            ->set('theme', 'neon')
            ->call('save')
            ->assertHasErrors(['theme' => 'in']);
    }

    public function test_settings_rejects_invalid_timezone(): void
    {
        Livewire::test(Settings::class)
            ->set('timezone', 'Not/AZone')
            ->call('save')
            ->assertHasErrors(['timezone' => 'timezone']);
    }

    public function test_settings_uppercases_currency(): void
    {
        Livewire::test(Settings::class)
            ->set('currency', 'jpy')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame('JPY', $this->user->refresh()->notification_preferences['currency']);
    }
}