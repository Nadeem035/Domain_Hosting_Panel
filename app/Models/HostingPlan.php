<?php

namespace App\Models;

use App\Enums\BillingCycle;
use App\Models\Traits\BelongsToTenant;
use Database\Factories\HostingPlanFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class HostingPlan extends Model
{
    /** @use HasFactory<HostingPlanFactory> */
    use BelongsToTenant, HasFactory, LogsActivity;

    protected $fillable = [
        'user_id',
        'panel_id',
        'name',
        'billing_cycle',
        'price',
        'disk_space',
        'bandwidth',
        'features',
        'description',
        'is_active',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'billing_cycle' => BillingCycle::class,
            'price' => 'float',
            'features' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['panel_id', 'name', 'billing_cycle', 'price', 'disk_space', 'bandwidth', 'features', 'description', 'is_active', 'notes'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('hosting_plan');
    }

    public function panel(): BelongsTo
    {
        return $this->belongsTo(Panel::class);
    }

    public function services(): HasMany
    {
        return $this->hasMany(Service::class);
    }
}