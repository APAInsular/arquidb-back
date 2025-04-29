<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Expedient extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title',
        'number',
        'start_date',
        'end_date',
        'description',
        'site',
        'postal_code',
        'budget',
        'center_id',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'budget' => 'decimal:2',
    ];

    public function phases(): HasMany
    {
        return $this->hasMany(Phase::class);
    }

    public function people(): BelongsToMany
    {
        return $this->belongsToMany(Person::class, 'expedient_person');
    }

    public function center(): BelongsTo
    {
        return $this->belongsTo(Center::class);
    }

    public function scopeNumber($query, $number)
    {
        if ($number) {
            $query->where('number', 'LIKE', "%$number%");
        }
    }

    public function scopeTitle($query, $name)
    {
        if ($name) {
            $query->where('title', 'LIKE', "%$name%");
        }
    }

    public function scopePhase($query, $phase)
    {
        if ($phase) {
            $query->whereHas('phases', function ($q) use ($phase) {
                $q->where('phase', 'LIKE', "%$phase%");
            });
        }
    }

    public function scopeClient($query, $client)
    {
        if ($client) {
            $query->whereHas('client', function ($q) use ($client) {
                $q->where('name', 'LIKE', "%$client%");
            });
        }
    }

    public function scopeCollegiate($query, $collegiate)
    {
        if ($collegiate) {
            $query->whereHas('collegiate', function ($q) use ($collegiate) {
                $q->where('name', 'LIKE', "%$collegiate%");
            });
        }
    }

    public function scopeDateCreated($query, $date)
    {
        if ($date) {
            $query->whereDate('created_at', '==', $date);
        }
    }
}
