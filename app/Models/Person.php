<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Person extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'identification_type',
        'identification_number',
        'name',
        'first_surname',
        'second_surname',
        'observations',
        'center_id'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
    ];

    public function expedients(): BelongsToMany
    {
        return $this->belongsToMany(Expedient::class, 'expedient_person');
    }

    public function collegiates(): HasOne
    {
        return $this->hasOne(Collegiate::class);
    }

    public function client(): HasOne
    {
        return $this->hasOne(Client::class);
    }

    public function phones(): HasMany
    {
        return $this->hasMany(Phone::class);
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class);
    }

    public function emails(): HasMany
    {
        return $this->hasMany(Email::class);
    }

    public function center(): BelongsTo
    {
        return $this->belongsTo(Center::class);
    }

    public function scopeCenters($query, $centerId)
    {
        return $query->whereHas('client', function ($q) use ($centerId) {
            $q->where('center_id', $centerId);
        });
    }

    public function scopeCentersCollegiate($query, $centerId)
    {
        return $query->whereHas('collegiates', function ($q) use ($centerId) {
            $q->where('center_id', $centerId);
        });
    }

    public function scopeName($query, $name)
    {
        if ($name) {
            $query->where('name', 'LIKE', "%$name%");
        }
    }
}
