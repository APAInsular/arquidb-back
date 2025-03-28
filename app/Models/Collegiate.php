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
        'birthDate',
        'nationality',
        'bankingEntity',
        'accountNumber',
        'college',
        'originCollege',
        'originCollegeNumber',
        'degree',
        'collegiateNumber',
        'specialty',
        'terminationDate',
        'graduationDate',
        'careerEndET',
        'webPage',
        'superiorCouncilRegNumber',
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
        'birthDate' => 'date',
        'terminationDate' => 'date',
        'graduationDate' => 'date',
    ];

    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }
}
