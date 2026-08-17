<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'company_name',
        'timezone',
        'theme_preference',
        'notification_preferences',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'notification_preferences' => 'array',
        ];
    }

    /**
     * Whether email notifications are enabled for the given tier.
     */
    public function wantsEmailFor(string $tier): bool
    {
        $prefs = $this->notification_preferences ?? [];

        if (array_key_exists('email', $prefs) && is_array($prefs['email']) && array_key_exists($tier, $prefs['email'])) {
            return (bool) $prefs['email'][$tier];
        }

        return true;
    }

    /**
     * The default currency for this reseller.
     */
    public function defaultCurrency(): string
    {
        return $this->notification_preferences['currency'] ?? 'USD';
    }
}
