<?php

namespace App\Livewire\Pages\Settings;

use App\Enums\ReminderTier;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Settings extends Component
{
    public string $theme = 'system';

    public string $timezone = 'UTC';

    public string $currency = 'USD';

    public bool $emailEnabled = true;

    /** @var array<string, bool> */
    public array $emailTiers = [];

    public function mount(): void
    {
        $user = auth()->user();
        $prefs = $user->notification_preferences ?? [];

        $this->theme = $user->theme_preference ?? 'system';
        $this->timezone = $user->timezone ?? 'UTC';
        $this->currency = $prefs['currency'] ?? 'USD';
        $this->emailEnabled = (bool) ($prefs['email_enabled'] ?? true);

        $this->emailTiers = collect(ReminderTier::cases())
            ->mapWithKeys(fn (ReminderTier $tier) => [
                $tier->value => (bool) ($prefs['email'][$tier->value] ?? true),
            ])
            ->all();
    }

    public function rules(): array
    {
        return [
            'theme' => ['required', Rule::in(['light', 'dark', 'system'])],
            'timezone' => ['required', 'timezone'],
            'currency' => ['required', 'string', 'size:3', 'alpha'],
            'emailEnabled' => ['boolean'],
            'emailTiers.*' => ['boolean'],
        ];
    }

    public function save(): void
    {
        $this->validate();

        $user = auth()->user();
        $prefs = $user->notification_preferences ?? [];
        $prefs['currency'] = strtoupper($this->currency);
        $prefs['email_enabled'] = $this->emailEnabled;
        $prefs['email'] = collect(ReminderTier::cases())
            ->mapWithKeys(fn (ReminderTier $tier) => [
                $tier->value => (bool) ($this->emailTiers[$tier->value] ?? false),
            ])
            ->all();

        $user->update([
            'theme_preference' => $this->theme,
            'timezone' => $this->timezone,
            'notification_preferences' => $prefs,
        ]);

        $this->dispatch('toast', message: 'Settings saved.', type: 'success');
    }

    /**
     * A flat list of timezone identifiers for the select.
     */
    public function timezones(): Collection
    {
        return collect(\DateTimeZone::listIdentifiers())
            ->filter(fn (string $tz) => str_contains($tz, '/') || $tz === 'UTC')
            ->values();
    }

    public function render()
    {
        return view('livewire.settings.index');
    }
}