<?php

namespace App\Models;

use App\Enums\ClientStatus;
use App\Models\Traits\BelongsToTenant;
use Database\Factories\ClientFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Client extends Model
{
    /** @use HasFactory<ClientFactory> */
    use BelongsToTenant, HasFactory, LogsActivity, SoftDeletes;

    protected $fillable = [
        'user_id',
        'name',
        'email',
        'phone',
        'company',
        'address',
        'notes',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => ClientStatus::class,
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'email', 'phone', 'company', 'address', 'notes', 'status'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('client');
    }

    public function services(): HasMany
    {
        return $this->hasMany(Service::class);
    }
}