<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Enums\UserTier;

#[Fillable(['name', 'email', 'password', 'wa_number', 'avatar_url', 'status', 'google_id', 'auth_provider', 'email_verified_at', 'wa_verified_at', 'tier', 'boost_quota', 'is_banned'])]
#[Hidden(['password', 'remember_token', 'google_id'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'wa_verified_at' => 'datetime',
            'password' => 'hashed',
            'tier' => UserTier::class,
            'is_banned' => 'boolean',
            'tier_expired_at' => 'datetime',
        ];
    }

    public function jastipListings()
    {
        return $this->hasMany(JastipListing::class);
    }

    public function jastipRequests()
    {
        return $this->hasMany(JastipRequest::class);
    }

    public function prelovedListings()
    {
        return $this->hasMany(PrelovedListing::class);
    }

    public function prelovedRequests()
    {
        return $this->hasMany(PrelovedRequest::class);
    }

    public function getActiveItemCount(string $type): int
    {
        return match($type) {
            'jastip_listing' => $this->jastipListings()->where('status', 'ACTIVE')->count(),
            'jastip_request' => $this->jastipRequests()->where('status', 'OPEN')->count(),
            'preloved_listing' => $this->prelovedListings()->where('status', 'AVAILABLE')->count(),
            'preloved_request' => $this->prelovedRequests()->where('status', 'OPEN')->count(),
            default => 0,
        };
    }

    public function getMaxItemLimit(): int
    {
        return match($this->tier) {
            UserTier::BASIC => 3,
            UserTier::PLUS => 10,
            UserTier::PRO => 20,
        };
    }

    public function canAddItem(string $type): bool
    {
        return $this->getActiveItemCount($type) < $this->getMaxItemLimit();
    }
}