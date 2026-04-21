<?php

namespace App\Models;

use App\UserRole;
// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'role'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public function hasRole(UserRole|string $role): bool
    {
        $roleValue = $role instanceof UserRole ? $role->value : $role;

        return $this->role->value === $roleValue;
    }

    public function isAdmin(): bool
    {
        return $this->hasRole(UserRole::Admin);
    }

    public function isAnggota(): bool
    {
        return $this->hasRole(UserRole::Anggota);
    }

    public function dashboardRouteName(): string
    {
        return match ($this->role) {
            UserRole::Admin => 'admin.dashboard',
            UserRole::Anggota => 'anggota.dashboard',
        };
    }

    public function member(): HasOne
    {
        return $this->hasOne(Member::class);
    }

    public function borrowings(): HasManyThrough
    {
        return $this->hasManyThrough(Borrowing::class, Member::class);
    }

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
            'role' => UserRole::class,
        ];
    }
}
