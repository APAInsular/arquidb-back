<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\Collegiate;
use App\Models\Email;
use App\Models\Person;
use App\Models\Phone;
use DB;
use Illuminate\Http\Request;
use Orion\Concerns\DisableAuthorization;
use Orion\Concerns\DisablePagination;
use Orion\Http\Controllers\RelationController;
use Orion\Http\Requests\Request as OrionRequest;
use View;

class PersonCollegiatesController extends RelationController
{
    //
    use DisablePagination;
    use DisableAuthorization;

    protected $model = Person::class;

    protected $relation = 'collegiates';

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
                $person->collegiates()->create($request->get('collegiate'));
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
                'person' => $person->load(['collegiate', 'email', 'address', 'phone']),
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al crear la persona',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(OrionRequest $request, ...$args)
    {

        $id = $args[0];

        try {
            $person = Person::findOrFail($id);

            $person->update([
                'identification_type' => $request->input('identification_type', $person->identification_type),
                'identification_number' => $request->input('identification_number', $person->identification_number),
                'name' => $request->input('name', $person->name),
                'first_surname' => $request->input('first_surname', $person->first_surname),
                'second_surname' => $request->input('second_surname', $person->second_surname),
                'observations' => $request->input('observations', $person->observations),
            ]);

            if ($request->has('collegiate')) {
                $person->collegiates()->updateOrCreate(
                    $request->get('collegiate')
                );
            }

            if ($request->has('email')) {
                $person->emails()->updateOrCreate(
                    $request->get('email')
                );
            }

            if ($request->has('address')) {
                $person->addresses()->updateOrCreate(
                    $request->get('address')
                );
            }

            if ($request->has('phone')) {
                $person->phones()->updateOrCreate(
                    $request->get('phone')
                );
            }

            return response()->json([
                'message' => 'Persona actualizada correctamente',
                'person' => $person->load(['collegiates', 'emails', 'addresses', 'phones']),
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al actualizar la persona',
                'error' => $e->getMessage(),
            ], 500);
        }

    }


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
