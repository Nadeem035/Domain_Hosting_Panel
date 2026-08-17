<?php

namespace Database\Factories;

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
            'disk_space' => fake()->randomElement(['5 GB', '10 GB', '20 GB', '50 GB', 'Unlimited']),
            'bandwidth' => fake()->randomElement(['50 GB', '100 GB', '500 GB', 'Unlimited']),
            'notes' => fake()->optional()->sentence(),
        ];
    }
}