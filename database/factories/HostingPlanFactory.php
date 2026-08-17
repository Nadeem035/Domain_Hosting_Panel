<?php

namespace Database\Factories;

use App\Enums\BillingCycle;
use App\Models\HostingPlan;
use App\Models\Panel;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<HostingPlan>
 */
class HostingPlanFactory extends Factory
{
    protected $model = HostingPlan::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'panel_id' => Panel::factory(),
            'name' => fake()->randomElement(['Starter 5GB', 'Business 20GB', 'Unlimited Pro', 'Shared Basic']),
            'billing_cycle' => fake()->randomElement(BillingCycle::cases()),
            'price' => fake()->randomFloat(2, 1, 50),
            'disk_space' => fake()->randomElement(['5 GB', '10 GB', '20 GB', '50 GB', 'Unlimited']),
            'bandwidth' => fake()->randomElement(['50 GB', '100 GB', '500 GB', 'Unlimited']),
            'features' => fake()->randomElements(['SSL certificate', 'Daily backups', 'Free domain', 'Email accounts', 'LiteSpeed cache'], 2),
            'description' => fake()->optional()->sentence(),
            'is_active' => true,
            'notes' => fake()->optional()->sentence(),
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['is_active' => false]);
    }
}