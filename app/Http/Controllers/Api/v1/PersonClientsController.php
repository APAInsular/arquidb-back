<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\Client;
use App\Models\Email;
use App\Models\Person;
use App\Models\Phone;
use Illuminate\Http\Request;
use Orion\Concerns\DisableAuthorization;
use Orion\Concerns\DisablePagination;
use Orion\Http\Controllers\RelationController;

class PersonClientsController extends RelationController
{
    //
    use DisablePagination;
    use DisableAuthorization;

    protected $model = Person::class;

    protected $relation = 'clients';

    public function Personclient($id)
    {
        $person = Person::findOrFail($id);

        $client = Client::where('person_id', $id)->get();
        $email = Email::where('person_id', $id)->get();
        $phone = Phone::where('person_id', $id)->get();
        $address = Address::where('person_id', $id)->get();

        if ($client->isEmpty()) {
            return response()->json([
                'message' => 'No se encontro ningun cliente'
            ], 404);
        }

        return response()->json([
            'person' => $person,
            'client' => $client,
            'email' => $email,
            'phone' => $phone,
            'address' => $address,
        ]);

    }
}
