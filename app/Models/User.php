<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Illuminate\Session\DatabaseSessionHandler;
use App\Models\Session;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
        'status',
        'last_status_updated',
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
            'last_status_updated' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->map(fn (string $name) => Str::of($name)->substr(0, 1))
            ->implode('');
    }

    public function sessions()
    {
        return $this->hasMany(Session::class, 'user_id', 'id');
    }

    public function role() {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function notifications() {
        return $this->hasMany(Notification::class, 'user_id');
    }

    /**
     * Set status and update the last_status_updated timestamp in one call.
     *
     * @param string $newStatus
     * @return $this
     */
    public function setStatus(string $newStatus)
    {
        $this->status = $newStatus;
        $this->last_status_updated = now();
        $this->save();

        return $this;
    }

    public function isActive(): bool
    {
        return $this->status === 'Active';
    }

    public function scopeOrdered(Builder $query, ?string $sort, ?string $direction): Builder
    {
        // Whitelist valid sort columns (and map "last_seen" → sessions_max_last_activity)
        $valid = ['id','name','email','role','created_at','updated_at','last_seen'];
        $sort = in_array($sort, $valid) ? $sort : 'id';
        $dir  = strtolower($direction) === 'desc' ? 'desc' : 'asc';

        if ($sort === 'last_seen') {
            // sessions_max_last_activity will exist because we will call withMax('sessions', 'last_activity')
            return $query->orderBy('sessions_max_last_activity', $dir);
        }

        if ($sort === 'role') {
            // join roles table so we can order by role name
            return $query
                ->join('roles', 'users.role_id', '=', 'roles.id')
                ->orderBy('roles.name', $dir)
                ->select('users.*');
        }

        // all others are direct columns on users
        return $query->orderBy($sort, $dir);
    }
}
