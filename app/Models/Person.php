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

    public function updateClient(array $clientData): void
    {
        $this->client()->update(
            $clientData
        );
    }

    // public function updateCollegiate(array $collegiate): void
    // {
    //     $collegiateDates = $this->collegiateDates($collegiate);
    //     $this->collegiates()->update($collegiateDates);
    // }

    // protected function collegiateDates(array $data): array
    // {
        
    //     return [
    //         'birth_date' => isset($data['birth_date']) ? substr($data['birth_date'], 0, 10) : null,
    //         'graduation_date' => isset($data['graduation_date']) ? substr($data['graduation_date'], 0, 10) : null,
    //         'termination_date' => isset($data['termination_date']) ? substr($data['termination_date'], 0, 10) : null,
    //     ];
    // }

    public function updateEmails(array $emails): void
    {
        $this->emails()->delete();
        $this->emails()->createMany($emails);
    }

    public function updateAddresses(array $addresses): void
    {
        $this->addresses()->delete();
        $this->addresses()->createMany($addresses);
    }

    public function updatePhones(array $phones): void
    {
        $this->phones()->delete();
        $this->phones()->createMany($phones);
    }

    public function updateRelations(array $request): void
    {
        if (isset($request['client'])) {
            $this->updateClient($request['client']);
        }

        if (isset($request['email'])) {
            $this->updateEmails($request['email']);
        }

        if (isset($request['address'])) {
            $this->updateAddresses($request['address']);
        }

        // if (isset($request['collegiate'])) {
        //     $this->updateCollegiate($request['collegiate']);
        // }
    }
}
