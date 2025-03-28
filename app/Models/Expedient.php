<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        'startDate',
        'endDate',
        'description',
        'site',
        'postalCode',
        'budget',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'startDate' => 'datetime',
        'endDate' => 'datetime',
        'budget' => 'decimal:2',
    ];

    public function phases(): HasMany
    {
        return $this->hasMany(Phase::class);
    }

    public function people(): BelongsToMany
    {
        return $this->belongsToMany(Person::class);
    }
}
