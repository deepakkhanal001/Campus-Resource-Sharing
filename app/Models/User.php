<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
        'role',
        'status',
        'college_student_id',
        'program',
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
        ];
    }

    public function resources(): HasMany
    {
        return $this->hasMany(Resource::class, 'owner_id');
    }

    public function borrowingRequests(): HasMany
    {
        return $this->hasMany(BorrowingRecord::class, 'requester_id');
    }

    public function ownedBorrowingRecords(): HasMany
    {
        return $this->hasMany(BorrowingRecord::class, 'owner_id');
    }

    public function appNotifications(): HasMany
    {
        return $this->hasMany(Notification::class, 'user_id');
    }

    public function isStudent(): bool
    {
        $role = strtolower((string) ($this->role ?? ''));
        $roles = method_exists($this, 'getRoleNames') ? $this->getRoleNames()->map(fn ($r) => strtolower((string) $r))->all() : [];
        return $role === 'student' || in_array('student', $roles, true);
    }

    public function isAdmin(): bool
    {
        $role = strtolower((string) ($this->role ?? ''));
        $roles = method_exists($this, 'getRoleNames') ? $this->getRoleNames()->map(fn ($r) => strtolower((string) $r))->all() : [];
        return $role === 'admin' || in_array('admin', $roles, true);
    }
}
