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
     * Admins are exempt from the read scope so they can see and manage every
     * tenant's data, but they still own what they create.
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

            if ($user) {
                $model->user_id = $user->getKey();
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Mirror the tenant global scope onto an arbitrary query builder for
     * raw validation closures (Rule::exists / Rule::unique) that run
     * against the query builder directly and skip Eloquent global scopes.
     * Admins are exempt, matching the scope in bootBelongsToTenant.
     */
    public static function tenantScopeQuery($query)
    {
        $user = auth()->user();

        if ($user && ! $user->hasRole('admin')) {
            $query->where((new static)->getTable().'.user_id', $user->getKey());
        }

        return $query;
    }
}