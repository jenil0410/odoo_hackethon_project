<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles, HasUuids, SoftDeletes;

    public $incrementing = false;

    protected $keyType = 'string';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'first_name',
        'last_name',
        'email',
        'phone_number',
        'profile_photo',
        'date_of_birth',
        'gender',
        'password',
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
            'date_of_birth' => 'date',
            'password' => 'hashed',
        ];
    }

    public function getRoleAttribute()
    {
        return $this->roles()->first();
    }

    public function getFullNameAttribute(): string
    {
        return trim(($this->first_name ?? '').' '.($this->last_name ?? ''));
    }

    public function getNameAttribute(): string
    {
        return $this->full_name;
    }

    public function setNameAttribute(?string $value): void
    {
        $name = trim((string) $value);
        $parts = $name === '' ? [] : preg_split('/\s+/', $name);

        $this->attributes['first_name'] = $parts[0] ?? '';
        $this->attributes['last_name'] = count($parts) > 1 ? implode(' ', array_slice($parts, 1)) : '';
    }

    public function getFirstNameAttribute(): string
    {
        return (string) ($this->attributes['first_name'] ?? '');
    }

    public function getLastNameAttribute(): string
    {
        return (string) ($this->attributes['last_name'] ?? '');
    }
}
