<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\CollegiateRequest;
use App\Http\Requests\PersonRequest;
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

class PersonClientsController extends Controller
{

    // use DisablePagination;
    // use DisableAuthorization;

    protected $model = Person::class;
    protected $relation = 'client';

    public function index(OrionRequest $request, ...$args)
    {
        $user = $request->user();
        $perSearch =
            $request->get('name')
            ?? $request->get('first_surname')
            ?? $request->get('second_surname')
            ?? $request->get('identification_number')
            ?? $request->get('observations');

        $page = $request->get('per_page');
        $all = $request->boolean('all', false);

        $query = Person::orderBy('id', 'Asc')
            ->searchPerson($perSearch)
            ->centers($user->center_id)
            ->whereHas('client')
            ->with('client');

        $all ? $clients = $query->get() : $clients = $query->paginate($page ? $page : 10);

        return response()->json($clients);

    }

    public function store(PersonRequest $request)
    {
        $user = $request->user();
        try {

            $person = Person::create([
                'identification_type' => $request->identification_type,
                'identification_number' => $request->identification_number,
                'name' => $request->name,
                'first_surname' => $request->first_surname,
                'second_surname' => $request->second_surname,
                'observations' => $request->observations,
                'center_id' => $user->center_id,
            ]);

            if (!empty($request->client)) {
                $person->client()->create($request->get('client'));
            }
            if (!empty($request->email)) {
                $person->emails()->createMany($request->get('email'));
            }
            if (!empty($request->address)) {
                $person->addresses()->createMany($request->get('address'));
            }
            if (!empty($request->phone)) {
                $person->phones()->createMany($request->get('phone'));
            }

            $record = Record::create([
                'user_id' => $request->user()->id,
                'name' => $request->user()->name,
                'action' => "create",
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

    public function update(PersonRequest $request, ...$args)
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

            $person->updateRelations($request->all());

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
                'affected_table' => "Client",
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
