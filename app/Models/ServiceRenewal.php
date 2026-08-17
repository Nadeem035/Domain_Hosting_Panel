<?php

namespace App\Models;

use Database\Factories\ServiceRenewalFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class ServiceRenewal extends Model
{
    /** @use HasFactory<ServiceRenewalFactory> */
    use HasFactory, LogsActivity;

    protected $fillable = [
        'service_id',
        'renewed_on',
        'previous_expiry_date',
        'new_expiry_date',
        'company_price',
        'client_price',
        'payment_received',
        'payment_received_date',
        'invoice_number',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'renewed_on' => 'date',
            'previous_expiry_date' => 'date',
            'new_expiry_date' => 'date',
            'company_price' => 'decimal:2',
            'client_price' => 'decimal:2',
            'payment_received' => 'boolean',
            'payment_received_date' => 'date',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'renewed_on',
                'previous_expiry_date',
                'new_expiry_date',
                'company_price',
                'client_price',
                'payment_received',
                'payment_received_date',
                'invoice_number',
                'notes',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('service_renewal');
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }
}