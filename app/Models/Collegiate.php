<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Collegiate extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'person_id',
        'birth_date',
        'nationality',
        'banking_entity',
        'account_number',
        'college',
        'origin_college',
        'origin_college_number',
        'degree',
        'collegiate_number',
        'specialty',
        'termination_date',
        'graduation_date',
        'career_end_et',
        'web_page',
        'council_reg_number',
        'situation',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'person_id' => 'integer',
        'birth_date' => 'date',
        'termination_date' => 'date',
        'graduation_date' => 'date',
    ];

    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }
}
