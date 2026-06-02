<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use App\Models\GoogleCalendarConnection;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token', 'two_factor_secret', 'two_factor_recovery_codes'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_enabled' => 'bool',
            'two_factor_recovery_codes' => 'array',
        ];
    }

    public function ownedDeals(): HasMany
    {
        return $this->hasMany(Deal::class, 'owner_id');
    }

    public function ownedEntities(): HasMany
    {
        return $this->hasMany(Entity::class, 'owner_id');
    }

    public function ownedPeople(): HasMany
    {
        return $this->hasMany(Person::class, 'owner_id');
    }

    public function ownedEvents(): HasMany
    {
        return $this->hasMany(CalendarEvent::class, 'owner_id');
    }

    public function googleCalendarConnection(): HasOne
    {
        return $this->hasOne(GoogleCalendarConnection::class, 'user_id');
    }
}
