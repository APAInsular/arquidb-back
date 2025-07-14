<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\ExpedientRequest;
use App\Models\Client;
use App\Models\Expedient;
use App\Models\Record;
use Gate;
use Orion\Concerns\DisableAuthorization;
use Orion\Concerns\DisablePagination;
// use Orion\Http\Controllers\Controller;
use Orion\Http\Requests\Request as OrionRequest;
use Illuminate\Http\Request;

class ExpedientController extends Controller
{
    // use DisableAuthorization;
    protected $model = Expedient::class;

    public function index(OrionRequest $request)
    {
        $user = $request->user();
        $number = $request->get('number');
        $title = $request->get('title');
        $phase = $request->get('phase');
        $client = $request->get('client');
        $collegiate = $request->get('collegiate');
        $dateFrom = $request->get('dateFrom');
        $dateTo = $request->get('dateTo');
        $page = $request->get('per_page');
        $all = $request->boolean('all', false);

        $query = Expedient::orderBy('id', 'Asc')
            ->number($number)
            ->title($title)
            ->phase($phase)
            ->client($client)
            ->collegiate($collegiate)
            ->dateFrom($dateFrom)
            ->dateTo($dateTo)
            ->centers($user->center_id);

        $query->with('people.client', 'people.collegiates', 'phases.documents');
        $all ? $expedients = $query->get() : $expedients = $query->paginate($page ? $page : 10);

        return response()->json($expedients);
    }

    public function show($id)
    {
        $expedient = Expedient::with('people.client', 'people.collegiates', 'phases.documents')
            ->findOrFail($id);

        return response()->json($expedient);
    }

    public function store(ExpedientRequest $request)
    {
        Gate::authorize('create', Expedient::class);
        $expedient = Expedient::create($request->validated());

        $record = Record::create([
            'user_id' => $request->user()->id,
            'name' => $request->user()->name,
            'action' => "create",
            'affected_table' => "expedient",
            'affected_record_id' => $expedient->id,
        ]);

        return response()->json([
            'message' => 'Expediente creado correctamente.',
            'data' => $expedient,
            'record' => $record,
        ]);
    }

    public function update(ExpedientRequest $request, $id)
    {

        $expedient = Expedient::findOrFail($id);
        Gate::authorize('update', $expedient);
        $validated = $request->validated();
        $expedient->update($validated);

        $record = Record::create([
            'user_id' => $request->user()->id,
            'name' => $request->user()->name,
            'action' => "update",
            'affected_table' => "expedient",
            'affected_record_id' => $expedient->id,
        ]);

        return response()->json([
            'message' => 'Expediente actualizado correctamente.',
            'data' => $expedient,
            'record' => $record,
        ]);
    }
    public function destroy($id)
    {
        $expedient = Expedient::findOrFail($id);

        Gate::authorize('delete', $expedient);
        $expedient->delete();

        $record = Record::create([
            'user_id' => auth()->user()->id,
            'name' => auth()->user()->name,
            'action' => "delete",
            'affected_table' => "expedient",
            'affected_record_id' => $expedient->id,
        ]);

        return response()->json([
            'message' => 'Expediente eliminado correctamente.',
            'data' => $expedient,
            'record' => $record,
        ]);
    }

    public function count(Request $request)
    {
        $user = $request->user();

        $query = Expedient::orderBy('id', 'Asc')
            ->centers($user->center_id)
            ->with('people.client', 'people.collegiates', 'phases.documents');
        $expedients = $query->get();

        $expedientCount = $expedients->count();
        $collegiateCount = 0;
        $clientCount = 0;
        $peopleCounted = collect([]);

        foreach ($expedients as $expedient) {
            foreach ($expedient->people as $person) {
                switch ($person->pivot->role) {
                    case 'collegiate':
                        if (!$peopleCounted->contains($person->id)) {
                            $peopleCounted->push($person->id);
                            $collegiateCount++;
                        }
                        break;
                    case 'client':
                        if (!$peopleCounted->contains($person->id)) {
                            $peopleCounted->push($person->id);
                            $clientCount++;
                        }
                        break;
                    default:
                        break;
                }
            }
        }

        return response()->json([
            'expedients_account' => $expedientCount,
            'collegiates_account' => $collegiateCount,
            'clients_account' => $clientCount,
        ]);
    }
}
