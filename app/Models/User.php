<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Traits\HasRoles;
use Auth;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'center_id',
        'google_id',
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
    protected $with = ['roles', 'roles.permissions', 'permissions'];

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }
    public function records(): HasMany
    {
        return $this->hasMany(Record::class);
    }
    public function center(): BelongsTo
    {
        return $this->belongsTo(Center::class);
    }

    public function scopeNameOrEmail($query, $search)
    {
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%$search%")
                    ->orWhere('email', 'LIKE', "%$search%");
            });
        }
    }

    public function scopeCenters($query, $centerId)
    {
        if (Auth::user()->hasRole('superAdmin')) {
            return $query;
        } else {
            return $query->where('center_id', $centerId);
        }
    }

    public function scopeUsers($query)
    {
        if (Auth::user()->hasRole('superAdmin')) {
            return $query;
        } else {
            return $query->whereDoesntHave('roles', function ($q) {
                $q->where('name', 'superAdmin');
            });
        }
    }
}
