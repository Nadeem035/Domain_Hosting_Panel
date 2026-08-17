<?php

namespace App\Models;

use App\Enums\ServiceStatus;
use App\Enums\ServiceType;
use App\Models\Traits\BelongsToTenant;
use Database\Factories\ServiceFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Service extends Model
{
    /** @use HasFactory<ServiceFactory> */
    use BelongsToTenant, HasFactory, LogsActivity, SoftDeletes;

    protected $fillable = [
        'user_id',
        'client_id',
        'panel_id',
        'hosting_plan_id',
        'type',
        'domain_name',
        'created_date',
        'expiry_date',
        'client_reminder_date',
        'company_price',
        'client_price',
        'currency',
        'status',
        'auto_renew_tracking',
        'last_expiry_tier',
        'last_payment_tier',
        'last_notified_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'type' => ServiceType::class,
            'status' => ServiceStatus::class,
            'created_date' => 'date',
            'expiry_date' => 'date',
            'client_reminder_date' => 'date',
            'company_price' => 'decimal:2',
            'client_price' => 'decimal:2',
            'auto_renew_tracking' => 'boolean',
            'last_notified_at' => 'datetime',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'client_id',
                'panel_id',
                'hosting_plan_id',
                'type',
                'domain_name',
                'created_date',
                'expiry_date',
                'client_reminder_date',
                'company_price',
                'client_price',
                'currency',
                'status',
                'auto_renew_tracking',
                'notes',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('service');
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function panel(): BelongsTo
    {
        return $this->belongsTo(Panel::class);
    }

    public function hostingPlan(): BelongsTo
    {
        return $this->belongsTo(HostingPlan::class);
    }

    public function renewals(): HasMany
    {
        return $this->hasMany(ServiceRenewal::class)->latest('renewed_on');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', ServiceStatus::Active->value);
    }

    /**
     * Not expired and not cancelled — everything still in the reminder engine.
     */
    public function scopeTracked(Builder $query): Builder
    {
        return $query->where('status', '!=', ServiceStatus::Cancelled->value)
            ->where('auto_renew_tracking', true);
    }

    /**
     * Reseller's profit margin on this service.
     */
    public function profit(): float
    {
        return (float) $this->client_price - (float) $this->company_price;
    }
}