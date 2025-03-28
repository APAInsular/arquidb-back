<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExpedientHasPerson extends Model
{

    protected $table = 'expedient_person';
    /** @use HasFactory<\Database\Factories\ExpedientHasPersonFactory> */
    use HasFactory;

    protected $fillable = [
        'expedient_id',
        'person_id',
    ];

    public function expedient(): BelongsTo
    {
        return $this->belongsTo(Expedient::class);
    }

    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }
}
