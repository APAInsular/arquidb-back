<?php

namespace App\Models;

use Auth;
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
        return $this->belongsToMany(Expedient::class, 'expedient_person')->withPivot('role');
    }

    public function collegiate(): HasOne
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
        if (Auth::user()->hasRole('superAdmin')) {
            return $query;
        } else {
            return $query->whereHas('client', function ($q) use ($centerId) {
                $q->where('center_id', $centerId);
            });
        }
    }

    public function scopeCentersCollegiate($query, $centerId)
    {

        if (Auth::user()->hasRole('superAdmin')) {
            return $query;
        } else {
            return $query->whereHas('collegiate', function ($q) use ($centerId) {
                $q->where('center_id', $centerId);
            });
        }
    }

    public function scopeSearchPerson($query, $search)
    {

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%$search%")
                    ->orWhere('first_surname', 'LIKE', "%$search%")
                    ->orWhere('second_surname', 'LIKE', "%$search%")
                    ->orWhere('identification_number', 'LIKE', "%$search%");
            });
        }
    }

    public function scopeSearchCollegiate($query, $search)
    {
        if ($search) {
            $query->where('collegiate', function ($q) use ($search) {

                $q->orWhere('birth_date', 'LIKE', "%$search%")
                    ->orWhere('nationality', 'LIKE', "%$search%")
                    ->orWhere('banking_entity', 'LIKE', "%$search%")
                    ->orWhere('account_number', 'LIKE', "%$search%")
                    ->orWhere('college', 'LIKE', "%$search%")
                    ->orWhere('degree', 'LIKE', "%$search%")
                    ->orWhere('collegiate_number', 'LIKE', "%$search%")
                    ->orWhere('termination_date', 'LIKE', "%$search%")
                    ->orWhere('graduation_date', 'LIKE', "%$search%")
                    ->orWhere('career_end_et', 'LIKE', "%$search%")
                    ->orWhere('web_page', 'LIKE', "%$search%")
                    ->orWhere('council_reg_number', 'LIKE', "%$search%")
                    ->orWhere('situation', 'LIKE', "%$search%");
            });
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
    //     $this->collegiate()->update($collegiateDates);
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

        if (isset($request['phone'])) {
            $this->updatePhones($request['phone']);
        }

        // if (isset($request['collegiate'])) {
        //     $this->updateCollegiate($request['collegiate']);
        // }
    }
}
