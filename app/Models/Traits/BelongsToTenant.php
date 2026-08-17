<?php

namespace App\Models\Traits;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToTenant
{
    /**
     * Boot the trait: apply a row-level tenant scope and auto-fill user_id.
     * Admins are exempt so they can see and manage every tenant's data.
     */
    public static function bootBelongsToTenant(): void
    {
        static::addGlobalScope('tenant', function (Builder $builder) {
            $user = auth()->user();

            if ($user && ! $user->hasRole('admin')) {
                $builder->where($builder->getModel()->qualifyColumn('user_id'), $user->getKey());
            }
        });

        static::creating(function (Model $model) {
            $user = auth()->user();

            if ($user && ! $user->hasRole('admin')) {
                $model->user_id = $user->getKey();
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}