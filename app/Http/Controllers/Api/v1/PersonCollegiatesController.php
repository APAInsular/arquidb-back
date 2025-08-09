<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\CollegiateRequest;
use App\Http\Requests\PersonRequest;
use App\Models\Address;
use App\Models\Collegiate;
use App\Models\Email;
use App\Models\Person;
use App\Models\Phone;
use App\Models\Record;
use DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Log;
use Orion\Concerns\DisableAuthorization;
use Orion\Concerns\DisablePagination;
use Orion\Http\Controllers\RelationController;
use Orion\Http\Requests\Request as OrionRequest;
use View;

class PersonCollegiatesController extends Controller
{

    // use DisableAuthorization;

    protected $model = Person::class;

    protected $relation = 'collegiate';

    public function index(OrionRequest $request, ...$args)
    {
        $user = $request->user();
        $perSearch =
            $request->get('name')
            ?? $request->get('first_surname')
            ?? $request->get('second_surname')
            ?? $request->get('identification_number');
        $page = $request->get('per_page');
        $all = $request->boolean('all', false);

        $query = Person::orderBy('id', 'Asc')
            ->searchPerson($perSearch)
            ->centersCollegiate($user->center_id)
            ->whereHas('collegiate')
            ->with('collegiate');

        $all ? $collegiates = $query->get() : $collegiates = $query->paginate($page ? $page : 10);

        return response()->json($collegiates);
    }

    public function store(PersonRequest $request)
    {
        try {
            $user = $request->user();
            $person = Person::create([
                'identification_type' => $request->identification_type,
                'identification_number' => $request->identification_number,
                'name' => $request->name,
                'first_surname' => $request->first_surname,
                'second_surname' => $request->second_surname,
                'observations' => $request->observations,
                'center_id' => $user->center_id,
            ]);

            if (!empty($request->collegiate)) {

                $data = $request->get('collegiate');

                $data['birth_date'] = isset($data['birth_date']) ? substr($data['birth_date'], 0, 10) : null;
                $data['graduation_date'] = isset($data['graduation_date']) ? substr($data['graduation_date'], 0, 10) : null;
                $data['termination_date'] = isset($data['termination_date']) ? substr($data['termination_date'], 0, 10) : null;

                $person->collegiate()->create($data);
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
                'affected_table' => "collegiates",
                'affected_record_id' => $person->id,
            ]);

            return response()->json([
                'message' => 'Persona creada correctamente',
                'person' => $person->load(['collegiates', 'emails', 'addresses', 'phones']),
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

            if ($request->has('collegiate')) {
                $data = $request->get('collegiate');
                $data['birth_date'] = isset($data['birth_date']) ? substr($data['birth_date'], 0, 10) : null;
                $data['graduation_date'] = isset($data['graduation_date']) ? substr($data['graduation_date'], 0, 10) : null;
                $data['termination_date'] = isset($data['termination_date']) ? substr($data['termination_date'], 0, 10) : null;

                $person->collegiate()->update($data);
            }

            $person->updateRelations($request->all());

            $record = Record::create([
                'user_id' => $request->user()->id,
                'name' => $request->user()->name,
                'action' => "update",
                'affected_table' => "collegiates",
                'affected_record_id' => $person->id,
            ]);

            return response()->json([
                'message' => 'Persona actualizada correctamente',
                'person' => $person->load(['collegiates', 'emails', 'addresses', 'phones']),
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

            $person->collegiate()->delete();
            $person->emails()->delete();
            $person->addresses()->delete();
            $person->phones()->delete();

            $person->delete();

            $record = Record::create([
                'user_id' => $request->user()->id,
                'name' => $request->user()->name,
                'action' => "delete",
                'affected_table' => "collegiate",
                'affected_record_id' => $person->id,
            ]);

            return response()->json([
                'message' => 'colegiado eliminado correctamente',
                'record' => $record
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error al eliminar al colegiado', [
                'exception' => $e,
            ]);

            return response()->json([
                'message' => 'Error al eliminar al colegiado',
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

    public function count(Request $request)
    {
        $user = $request->user();

        $query = Person::orderBy('id', 'Asc')
            ->centersCollegiate($user->center_id)
            ->whereHas('collegiate')
            ->with('collegiate');
        $collegiates = $query->get();

        $collegiateCount = $collegiates->count();

        return response()->json([
            'collegiates_account' => $collegiateCount,
        ]);
    }
}
