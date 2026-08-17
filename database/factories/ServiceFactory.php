<?php

namespace Database\Factories;

use App\Enums\ServiceStatus;
use App\Enums\ServiceType;
use App\Models\Client;
use App\Models\HostingPlan;
use App\Models\Panel;
use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Service>
 */
class ServiceFactory extends Factory
{
    protected $model = Service::class;

    public function definition(): array
    {
        $createdDate = fake()->dateTimeBetween('-2 years', '-6 months');

        return [
            'user_id' => User::factory(),
            'client_id' => Client::factory(),
            'panel_id' => Panel::factory(),
            'hosting_plan_id' => null,
            'type' => ServiceType::Domain,
            'domain_name' => fake()->domainName(),
            'created_date' => $createdDate->format('Y-m-d'),
            'expiry_date' => fake()->dateTimeBetween('-30 days', '+11 months')->format('Y-m-d'),
            'client_reminder_date' => null,
            'company_price' => fake()->randomFloat(2, 5, 60),
            'client_price' => fn (array $attrs) => round($attrs['company_price'] * 1.6, 2),
            'currency' => 'USD',
            'status' => ServiceStatus::Active,
            'auto_renew_tracking' => true,
            'notes' => fake()->optional()->sentence(),
        ];
    }

    public function hosting(): static
    {
        return $this->state(function () {
            $panel = Panel::factory()->hosting()->create();

            return [
                'type' => ServiceType::Hosting,
                'panel_id' => $panel,
                'hosting_plan_id' => HostingPlan::factory()->for($panel),
                'domain_name' => null,
            ];
        });
    }

    public function both(): static
    {
        return $this->state(fn () => ['type' => ServiceType::Both]);
    }

    public function cancelled(): static
    {
        return $this->state(fn () => ['status' => ServiceStatus::Cancelled]);
    }

    public function expired(): static
    {
        return $this->state(fn () => [
            'status' => ServiceStatus::Expired,
            'expiry_date' => now()->subDays(10)->toDateString(),
        ]);
    }

    public function withExpiry(string $date): static
    {
        return $this->state(fn () => ['expiry_date' => $date]);
    }

    public function withReminderDate(string $date): static
    {
        return $this->state(fn () => ['client_reminder_date' => $date]);
    }

    public function untracked(): static
    {
        return $this->state(fn () => ['auto_renew_tracking' => false]);
    }
}