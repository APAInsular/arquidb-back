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
use Orion\Http\Requests\Request as OrionRequest;

class PersonClientsController extends RelationController
{
    //
    use DisablePagination;
    use DisableAuthorization;

    protected $model = Person::class;

    protected $relation = 'clients';

    public function store(OrionRequest $request, ...$args)
    {
        try {
            // 1. Crear la persona
            $person = Person::create([
                'identification_type' => $request->identification_type,
                'identification_number' => $request->identification_number,
                'name' => $request->name,
                'first_surname' => $request->first_surname,
                'second_surname' => $request->second_surname,
                'observations' => $request->observations,
            ]);

            if (!empty($request->collegiate)) {
                $person->clients()->create($request->get('client'));
            }

            if (!empty($request->email)) {
                $person->emails()->create($request->get('email'));
            }

            if (!empty($request->address)) {
                $person->addresses()->create($request->get('address'));
            }

            if (!empty($request->phone)) {
                $person->phones()->create($request->get('phone'));
            }

            return response()->json([
                'message' => 'Persona creada correctamente',
                'person' => $person->load(['client', 'email', 'address', 'phone']),
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al crear la persona',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

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
