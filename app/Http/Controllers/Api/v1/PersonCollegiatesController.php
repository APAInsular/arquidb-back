<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\Collegiate;
use App\Models\Email;
use App\Models\Person;
use App\Models\Phone;
use Illuminate\Http\Request;
use Orion\Concerns\DisableAuthorization;
use Orion\Concerns\DisablePagination;
use Orion\Http\Controllers\RelationController;
use View;

class PersonCollegiatesController extends RelationController
{
    //
    use DisablePagination;
    use DisableAuthorization;

    protected $model = Person::class;

    protected $relation = 'collegiates';

    public function Personcollegiate($id)
    {
        $person = Person::findOrFail($id);

        $collegiate = Collegiate::where('person_id', $id)->get();
        $email = Email::where('person_id', $id)->get();
        $phone = Phone::where('person_id', $id)->get();
        $address = Address::where('person_id', $id)->get();

        if ($collegiate->isEmpty()) {
            return response()->json([
                'message' => 'No se encontro ningun colegiado',
            ], 404);
        }

        return response()->json([
            'person' => $person,
            'collegiate' => $collegiate,
            'email' => $email,
            'phone' => $phone,
            'address' => $address,
        ]);


    }
}
