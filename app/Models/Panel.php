<?php

namespace App\Models;

use App\Enums\PanelType;
use App\Models\Traits\BelongsToTenant;
use Database\Factories\PanelFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Panel extends Model
{
    /** @use HasFactory<PanelFactory> */
    use BelongsToTenant, HasFactory, LogsActivity;

    protected $fillable = [
        'user_id',
        'name',
        'type',
        'login_url',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'type' => PanelType::class,
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'type', 'login_url', 'notes'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('panel');
    }

    public function hostingPlans(): HasMany
    {
        return $this->hasMany(HostingPlan::class);
    }

    public function services(): HasMany
    {
        return $this->hasMany(Service::class);
    }
}