<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\Client;
use App\Models\Email;
use App\Models\Person;
use App\Models\Phone;
use App\Models\Record;
use Auth;
use Illuminate\Http\Request;
use Log;
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

    protected $relation = 'client';

    public function index(OrionRequest $request, ...$args)
    {
        $user = $request->user();
        $name = $request->get('name');
        $page = $request->get('per_page');
        $all = $request->boolean('all', false);

        $query = Person::orderBy('id', 'Asc')
            ->name($name)
            // ->centers($user->center_id)
            ->whereHas('client')
            ->with('client');

        $all ? $clients = $query->get() : $clients = $query->paginate($page ? $page : 10);

        return response()->json($clients);

    }

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

            if (!empty($request->client)) {
                $person->client()->create($request->get('client'));
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

            $record = Record::create([
                'user_id' => $request->user()->id,
                'name' => $request->user()->name,
                'action' => "sign",
                'affected_table' => "Client",
                'affected_record_id' => $person->id,
            ]);

            return response()->json([
                'message' => 'Persona creada correctamente',
                'person' => $person->load(['client', 'emails', 'addresses', 'phones']),
                'record' => $record
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

            if ($request->has('client')) {
                $person->client()->first()->update(
                    $request->get('client')
                );
            }

            if ($request->has('email')) {
                $person->emails()->update(
                    $request->get('email')
                );
            }

            if ($request->has('address')) {
                $person->addresses()->update(
                    $request->get('address')
                );
            }

            if ($request->has('phone')) {
                $person->phones()->update(
                    $request->get('phone')
                );
            }

            $record = Record::create([
                'user_id' => $request->user()->id,
                'name' => $request->user()->name,
                'action' => "update",
                'affected_table' => "Client",
                'affected_record_id' => $person->id,
            ]);


            return response()->json([
                'message' => 'Persona actualizada correctamente',
                'person' => $person->load(['client', 'emails', 'addresses', 'phones']),
                'record' => $record
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error al actualizar persona', [
                'exception' => $e,
                'request_data' => $request->all(),
            ]);

            return response()->json([
                'message' => 'Error al actualizar la persona',
                'error' => $e->getMessage(),
            ], 500);
        }

    }

    public function destroy(OrionRequest $request, ...$args)
    {
        $id = $args[0];

        try {
            $person = Person::findOrFail($id);

            $person->client()->delete();
            $person->emails()->delete();
            $person->addresses()->delete();
            $person->phones()->delete();

            $person->delete();

            $record = Record::create([
                'user_id' => $request->user()->id,
                'name' => $request->user()->name,
                'action' => "delete",
                'affected_table' => "Person, Client, Email, Address, Phone",
                'affected_record_id' => $person->id,
            ]);

            return response()->json([
                'message' => 'Cliente eliminado correctamente',
                'record' => $record
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error al eliminar cliente', [
                'exception' => $e,
            ]);

            return response()->json([
                'message' => 'Error al eliminar cliente',
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
