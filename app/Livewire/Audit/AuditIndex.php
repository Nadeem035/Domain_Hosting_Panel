<?php

namespace App\Livewire\Audit;

use App\Models\Client;
use App\Models\HostingPlan;
use App\Models\Panel;
use App\Models\Service;
use App\Models\ServiceRenewal;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Activitylog\Models\Activity;

#[Layout('layouts.app')]
class AuditIndex extends Component
{
    use WithPagination;

    #[Url(as: 'q', history: true)]
    public string $search = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function activities()
    {
        $query = Activity::query()
            ->with('causer:id,name')
            ->when(! auth()->user()->hasRole('admin'), fn (Builder $q) => $this->scopeToTenant($q))
            ->when($this->search !== '', fn (Builder $q) => $q->where('description', 'like', '%'.$this->search.'%'))
            ->orderByDesc('created_at');

        return $query->paginate(25);
    }

    /**
     * Non-admins only see activity they caused or activity on their own records.
     */
    private function scopeToTenant(Builder $query): Builder
    {
        return $query->where(function (Builder $q) {
            $q->where(fn (Builder $q) => $q
                ->where('causer_type', User::class)
                ->where('causer_id', auth()->id()));

            $owned = [
                Client::class => Client::query()->pluck('id'),
                Panel::class => Panel::query()->pluck('id'),
                HostingPlan::class => HostingPlan::query()->pluck('id'),
                Service::class => Service::query()->pluck('id'),
                ServiceRenewal::class => ServiceRenewal::query()->pluck('id'),
            ];

            foreach ($owned as $type => $ids) {
                if ($ids->isEmpty()) {
                    continue;
                }

                $q->orWhere(fn (Builder $q) => $q
                    ->where('subject_type', $type)
                    ->whereIn('subject_id', $ids));
            }
        });
    }

    /**
     * Human label + route for an activity's subject, if it still exists.
     *
     * @return array{label: string, url: string|null}
     */
    public function subjectFor(Activity $activity): array
    {
        $subject = $activity->subject;

        return match (true) {
            $subject instanceof Client => ['label' => $subject->name, 'url' => route('clients.show', $subject)],
            $subject instanceof Service => ['label' => $subject->domain_name ?: 'Service #'.$subject->id, 'url' => route('services.show', $subject)],
            $subject instanceof Panel => ['label' => $subject->name, 'url' => route('panels.show', $subject)],
            $subject instanceof HostingPlan => ['label' => $subject->name, 'url' => $subject->panel ? route('panels.show', $subject->panel) : null],
            $subject instanceof ServiceRenewal => ['label' => $subject->invoice_number ?: 'Renewal #'.$subject->id, 'url' => $subject->service ? route('services.show', $subject->service) : null],
            default => ['label' => $activity->subject_type ? class_basename($activity->subject_type).' #'.$activity->subject_id : 'System', 'url' => null],
        };
    }

    /**
     * Names of the fields that changed on an update event.
     *
     * @return list<string>
     */
    public function changedFields(Activity $activity): array
    {
        if ($activity->event !== 'updated') {
            return [];
        }

        return array_keys($activity->properties->get('attributes', []));
    }

    public function render()
    {
        return view('livewire.audit.index', [
            'activities' => $this->activities,
        ]);
    }
}