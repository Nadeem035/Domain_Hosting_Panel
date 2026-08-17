<?php

namespace Database\Seeders;

use App\Enums\ClientStatus;
use App\Enums\PanelType;
use App\Enums\ServiceStatus;
use App\Enums\ServiceType;
use App\Models\Client;
use App\Models\HostingPlan;
use App\Models\Panel;
use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $staffRole = Role::findOrCreate('staff', 'web');
        $resellerRole = Role::findOrCreate('reseller', 'web');

        $staff = User::updateOrCreate(
            ['email' => 'staff@example.com'],
            [
                'name' => 'Staff Member',
                'password' => 'password',
                'company_name' => 'Demo Hosting Co',
                'email_verified_at' => now(),
            ]
        );
        $staff->syncRoles([$staffRole]);

        $reseller = User::updateOrCreate(
            ['email' => 'reseller@example.com'],
            [
                'name' => 'Reseller Owner',
                'password' => 'password',
                'company_name' => 'Reseller Agency Ltd',
                'email_verified_at' => now(),
            ]
        );
        $reseller->syncRoles([$resellerRole]);

        $this->seedTenant($staff, 2);
        $this->seedTenant($reseller, 4);
        $this->seedTenant(User::where('email', 'admin@example.com')->firstOrFail(), 2);
    }

    private function seedTenant(User $user, int $clientCount): void
    {
        if (Client::where('user_id', $user->id)->exists()) {
            return;
        }

        $clients = Client::factory()->for($user)->count($clientCount)->create([
            'status' => ClientStatus::Active,
        ]);

        $hostingPanel = Panel::factory()->for($user)->create([
            'name' => 'Shared cPanel',
            'type' => PanelType::Hosting,
        ]);
        $domainPanel = Panel::factory()->for($user)->create([
            'name' => 'Domain Registrar',
            'type' => PanelType::Domain,
        ]);

        $starter = HostingPlan::factory()->for($user)->for($hostingPanel)->create([
            'name' => 'Starter 5GB',
            'disk_space' => '5 GB',
            'bandwidth' => '50 GB',
        ]);
        $business = HostingPlan::factory()->for($user)->for($hostingPanel)->create([
            'name' => 'Business 20GB',
            'disk_space' => '20 GB',
            'bandwidth' => '100 GB',
        ]);

        $client = $clients->first();
        $other = $clients->skip(1)->first();

        Service::factory()->for($user)->for($client)->for($domainPanel)->create([
            'type' => ServiceType::Domain,
            'status' => ServiceStatus::Active,
        ]);
        Service::factory()->for($user)->for($client)->for($domainPanel)->create([
            'type' => ServiceType::Domain,
            'status' => ServiceStatus::Active,
            'expiry_date' => now()->addDays(12)->toDateString(),
        ]);
        Service::factory()->for($user)->for($other)->for($domainPanel)->create([
            'type' => ServiceType::Domain,
            'status' => ServiceStatus::Expired,
            'expiry_date' => now()->subDays(8)->toDateString(),
        ]);
        Service::factory()->for($user)->for($other)->for($hostingPanel)->for($starter)->create([
            'type' => ServiceType::Hosting,
            'status' => ServiceStatus::Active,
            'domain_name' => null,
        ]);
        Service::factory()->for($user)->for($other)->for($hostingPanel)->for($business)->create([
            'type' => ServiceType::Hosting,
            'status' => ServiceStatus::Active,
            'domain_name' => null,
        ]);
        Service::factory()->for($user)->for($client)->for($domainPanel)->create([
            'type' => ServiceType::Both,
            'status' => ServiceStatus::Cancelled,
        ]);
    }
}