<?php

namespace Database\Seeders;

use App\Enums\BillingCycle;
use App\Enums\ClientStatus;
use App\Enums\PanelType;
use App\Enums\ServiceStatus;
use App\Enums\ServiceType;
use App\Models\Client;
use App\Models\HostingPlan;
use App\Models\Panel;
use App\Models\Service;
use App\Models\ServiceRenewal;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Spatie\Permission\Models\Role;

class DemoDataSeeder extends Seeder
{
    /**
     * Realistic reseller portfolio used to give every example account a
     * populated dashboard, invoices list and renewal report.
     */
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

        $this->seedTenant($staff, 6, 3);
        $this->seedTenant($reseller, 12, 5);
        $this->seedTenant(User::where('email', 'admin@example.com')->firstOrFail(), 8, 5);
    }

    /**
     * Populate a full portfolio for one user: clients, panels, plans,
     * services across every status/tier, and a renewal history.
     */
    private function seedTenant(User $user, int $clientCount, int $panelCount): void
    {
        if (Client::where('user_id', $user->id)->exists()) {
            return;
        }

        $clients = $this->seedClients($user, $clientCount);
        $panels = $this->seedPanels($user, $panelCount);
        $plans = $this->seedPlans($user, $panels);

        $services = $this->seedServices($user, $clients, $panels, $plans);

        $this->seedRenewals($services);
    }

    /**
     * A varied client base, mostly active with a few inactive.
     *
     * @return Collection<int, Client>
     */
    private function seedClients(User $user, int $count): Collection
    {
        $clients = Client::factory()->for($user)->count($count)->create();

        // Mark roughly one in five as inactive so the status filter has data.
        $clients->slice(0, max(0, (int) floor($count / 5)))->each(
            fn (Client $client) => $client->update(['status' => ClientStatus::Inactive])
        );

        return $clients;
    }

    /**
     * A mix of server/control-panel types plus a domain registrar.
     *
     * @return Collection<int, Panel>
     */
    private function seedPanels(User $user, int $count): Collection
    {
        $specs = [
            ['name' => 'Shared cPanel Server', 'type' => PanelType::Cpanel, 'host' => 'srv1.example.com', 'ip' => '203.0.113.10', 'login' => 'https://srv1.example.com:2083'],
            ['name' => 'WHM Reseller Box', 'type' => PanelType::Whm, 'host' => 'whm.example.com', 'ip' => '203.0.113.45', 'login' => 'https://whm.example.com:2087'],
            ['name' => 'Plesk Node', 'type' => PanelType::Plesk, 'host' => 'plesk.example.com', 'ip' => '203.0.113.72', 'login' => 'https://plesk.example.com:8443'],
            ['name' => 'DirectAdmin VPS', 'type' => PanelType::DirectAdmin, 'host' => 'da.example.com', 'ip' => '203.0.113.99', 'login' => 'https://da.example.com:2222'],
            ['name' => 'Domain Registrar', 'type' => PanelType::Other, 'host' => 'registrar.example.net', 'ip' => '198.51.100.20', 'login' => 'https://registrar.example.net'],
        ];

        $panels = new Collection();

        foreach (array_slice($specs, 0, $count) as $spec) {
            $panels->push(Panel::factory()->for($user)->create([
                'name' => $spec['name'],
                'type' => $spec['type'],
                'host' => $spec['host'],
                'ip_address' => $spec['ip'],
                'login_url' => $spec['login'],
                'username' => $user->name === 'Admin' ? 'root' : 'reseller',
                'client_limit' => match ($spec['type']) {
                    PanelType::Whm, PanelType::DirectAdmin => 100,
                    PanelType::Plesk => 50,
                    default => 0,
                },
                'notes' => $spec['type'] === PanelType::Other
                    ? 'Used for domain registrations and transfers.'
                    : 'Primary hosting node for reseller accounts.',
            ]));
        }

        return $panels;
    }

    /**
     * Give each hosting-capable panel a set of plans across billing cycles.
     *
     * @param Collection<int, Panel> $panels
     *
     * @return Collection<int, HostingPlan>
     */
    private function seedPlans(User $user, Collection $panels): Collection
    {
        $planTemplates = [
            ['name' => 'Starter 5GB', 'disk' => '5 GB', 'bw' => '50 GB', 'price' => 2.99, 'cycle' => BillingCycle::Monthly, 'features' => ['1 website', 'SSL certificate', 'LiteSpeed cache']],
            ['name' => 'Business 20GB', 'disk' => '20 GB', 'bw' => '100 GB', 'price' => 4.99, 'cycle' => BillingCycle::Monthly, 'features' => ['Unlimited websites', 'SSL certificate', 'Daily backups']],
            ['name' => 'Pro 50GB', 'disk' => '50 GB', 'bw' => '250 GB', 'price' => 8.99, 'cycle' => BillingCycle::Monthly, 'features' => ['Unlimited websites', 'SSL certificate', 'Daily backups', 'Free domain']],
            ['name' => 'Agency Unlimited', 'disk' => 'Unlimited', 'bw' => 'Unlimited', 'price' => 14.99, 'cycle' => BillingCycle::Monthly, 'features' => ['Unlimited websites', 'SSL certificate', 'Daily backups', 'Free domain', 'Priority support']],
        ];

        $plans = new Collection();

        foreach ($panels as $index => $panel) {
            // Skip the domain registrar for hosting plans.
            if ($panel->type === PanelType::Other) {
                continue;
            }

            foreach ($planTemplates as $template) {
                $plans->push(HostingPlan::factory()->for($user)->for($panel)->create([
                    'name' => $template['name'],
                    'billing_cycle' => $template['cycle'],
                    'price' => $template['price'],
                    'disk_space' => $template['disk'],
                    'bandwidth' => $template['bw'],
                    'features' => $template['features'],
                    'description' => "Performance hosting plan for small to medium reseller websites.",
                    'is_active' => true,
                    'notes' => null,
                ]));
            }

            // A couple of annual variants for pricing-vs-cycle variety.
            $plans->push(HostingPlan::factory()->for($user)->for($panel)->create([
                'name' => 'Business 20GB - Annual',
                'billing_cycle' => BillingCycle::Annual,
                'price' => 49.99,
                'disk_space' => '20 GB',
                'bandwidth' => '100 GB',
                'features' => ['Unlimited websites', 'SSL certificate', 'Daily backups'],
                'description' => 'Discounted annual billing option.',
                'is_active' => true,
            ]));
        }

        return $plans;
    }

    /**
     * Build a realistic service portfolio covering every type, status and
     * reminder tier, attached to existing clients/panels/plans.
     *
     * @param Collection<int, Client>  $clients
     * @param Collection<int, Panel>   $panels
     * @param Collection<int, HostingPlan> $plans
     *
     * @return Collection<int, Service>
     */
    private function seedServices(User $user, Collection $clients, Collection $panels, Collection $plans): Collection
    {
        $registrar = $panels->firstWhere('type', PanelType::Other) ?? $panels->first();
        $hostingPanels = $panels->where('type', '!=', PanelType::Other)->values();
        $disposable = $clients->where('status', ClientStatus::Active->value)->values();
        $activePlans = $plans->filter(fn ($p) => $p->is_active)->values();
        $domains = [
            'acme-website.com', 'northwind-store.net', 'blueskyagency.org', 'greenvalley.co',
            'rapidcart.io', 'brightlabs.dev', 'ecomhub.shop', 'cloudpeak.cloud',
            'summitsports.co.uk', 'coastaldesigns.net', 'peakfinance.app', 'orchardmart.com',
        ];

        if ($disposable->isEmpty()) {
            $disposable = $clients;
        }

        $services = new Collection();

        // A few domain-only services across different tiers.
        foreach ($disposable->take(4) as $index => $client) {
            $offset = ($index * 3 + 5) % 60;
            $days = $index % 4 === 0 ? -6 : $offset; // one already expired
            $services->push(Service::factory()->for($user)->for($client)->for($registrar)->create([
                'type' => ServiceType::Domain,
                'status' => $index % 4 === 0 ? ServiceStatus::Expired : ServiceStatus::Active,
                'domain_name' => $domains[$index],
                'company_price' => 9.00,
                'client_price' => 14.00,
                'expiry_date' => now()->addDays($days)->toDateString(),
                'created_date' => now()->subMonths(8 + $index)->toDateString(),
            ]));
        }

        // Hosting services spread over the hosting panels + plans.
        foreach ($disposable->slice(0, 8) as $index => $client) {
            $panel = $hostingPanels[$index % max(1, $hostingPanels->count())];
            $plan = $activePlans[$index % max(1, $activePlans->count())];
            $cycle = $plan->billing_cycle;
            $expiry = now()->addDays(($index * 9 + 3) % 90);

            $services->push(Service::factory()->for($user)->for($client)->for($panel)->create([
                'type' => ServiceType::Hosting,
                'status' => $index % 7 === 0 ? ServiceStatus::PendingRenewal : ServiceStatus::Active,
                'hosting_plan_id' => $plan->id,
                'domain_name' => null,
                'company_price' => $plan->price * 0.7,
                'client_price' => $plan->price,
                'expiry_date' => $expiry->toDateString(),
                'created_date' => now()->subMonths($cycle->months() * 2 + 1)->toDateString(),
            ]));
        }

        // A couple of combined domain + hosting bundles, one cancelled.
        foreach ($disposable->slice(0, 3) as $index => $client) {
            $panel = $hostingPanels[$index % max(1, $hostingPanels->count())];
            $plan = $activePlans->first();
            $cancelled = $index === 2;

            $services->push(Service::factory()->for($user)->for($client)->for($panel)->create([
                'type' => ServiceType::Both,
                'status' => $cancelled ? ServiceStatus::Cancelled : ServiceStatus::Active,
                'hosting_plan_id' => $plan?->id,
                'domain_name' => $domains[$disposable->count() + $index] ?? ($domains[$index].'.com'),
                'company_price' => 14.00,
                'client_price' => 22.00,
                'expiry_date' => $cancelled ? now()->subDays(20)->toDateString() : now()->addDays(($index + 10) * 8)->toDateString(),
                'created_date' => now()->subMonths(10 + $index * 2)->toDateString(),
                'auto_renew_tracking' => ! $cancelled,
            ]));
        }

        return $services;
    }

    /**
     * Give each non-cancelled service a renewal history so the invoices page
     * and the revenue chart have something realistic to show.
     *
     * @param Collection<int, Service> $services
     */
    private function seedRenewals(Collection $services): void
    {
        $invoiceIndex = 1;

        foreach ($services->filter(fn ($s) => $s->status !== ServiceStatus::Cancelled->value) as $service) {
            $cycle = $service->hostingPlan?->billing_cycle ?? BillingCycle::Monthly;

            // Include this month for the revenue chart when the user is seeded.
            $includeRecent = $invoiceIndex % 3 === 0;

            $history = range(1, $invoiceIndex % 4 + 1);

            foreach ($history as $step) {
                $renewedOn = $includeRecent && $step === count($history)
                    ? now()->subDays(($invoiceIndex % 20) + 2)
                    : now()->subMonths($cycle->months() * $step + ($invoiceIndex % 3));

                $previousExpiry = $renewedOn->copy()->subMonths($cycle->months());
                $newExpiry = $renewedOn->copy()->addMonths($cycle->months());
                $paid = $step % 3 !== 0;

                ServiceRenewal::factory()->for($service)->create([
                    'user_id' => $service->user_id,
                    'renewed_on' => $renewedOn->toDateString(),
                    'previous_expiry_date' => $previousExpiry->toDateString(),
                    'new_expiry_date' => $newExpiry->toDateString(),
                    'company_price' => $service->company_price,
                    'client_price' => $service->client_price,
                    'payment_received' => $paid,
                    'payment_received_date' => $paid ? $renewedOn->addDays(2)->toDateString() : null,
                    'invoice_number' => 'INV-'.str_pad((string) $service->user_id, 3, '0', STR_PAD_LEFT).'-'.str_pad((string) $invoiceIndex, 3, '0', STR_PAD_LEFT),
                    'notes' => null,
                ]);

                $invoiceIndex++;
            }
        }
    }
}
