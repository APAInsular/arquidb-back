<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\CenterRequest;
use App\Models\Center;
use App\Models\Record;
use Gate;
use Orion\Concerns\DisableAuthorization;
use Orion\Concerns\DisablePagination;
// use Orion\Http\Controllers\Controller;
use Orion\Http\Requests\Request as OrionRequest;
use Illuminate\Http\Request;

class CenterController extends Controller
{
    // prueba
    // use DisableAuthorization, DisablePagination;

    protected $model = Center::class;

    public function index(OrionRequest $request)
    {

        Gate::authorize('view', Center::class);

        $search = $request->get('name') ?? $request->get('phone');
        $page = $request->get('per_page');
        $all = $request->boolean('all', false);

        $query = Center::orderBy('id', 'Asc')
            ->where('id', '!=', auth()->id())
            ->nameOrPhone($search);

        $all ? $centers = $query->get() : $centers = $query->paginate($page ? $page : 10);

        return response()->json($centers);

    }

    public function show($id)
    {
        $center = Center::findOrFail($id);
        Gate::authorize('view', $center);

        return response()->json($center);
    }

    public function store(CenterRequest $request)
    {
        Gate::authorize('create', Center::class);

        $validated = $request->validated();
        $center = Center::create($validated);

        $record = Record::create([
            'user_id' => $request->user()->id,
            'name' => $request->user()->name,
            'action' => "create",
            'affected_table' => "centers",
            'affected_record_id' => $center->id,
        ]);

        return response()->json([
            'message' => 'Centro creado correctamente.',
            'data' => $center,
            'record' => $record,
        ], 201);
    }

    public function update(CenterRequest $request, $id)
    {
        $center = Center::findOrFail($id);
        Gate::authorize('update', $center);
        $validated = $request->validated();
        $center->update($validated);

        $record = Record::create([
            'user_id' => $request->user()->id,
            'name' => $request->user()->name,
            'action' => "update",
            'affected_table' => "centers",
            'affected_record_id' => $center->id,
        ]);

        return response()->json([
            'message' => 'Centro actualizado correctamente.',
            'data' => $center,
            'record' => $record,
        ]);
    }

    public function destroy($id)
    {
        $center = Center::findOrFail($id);
        Gate::authorize('delete', $center);

        $center->delete();

        $record = Record::create([
            'user_id' => auth()->user()->id,
            'name' => auth()->user()->name,
            'action' => "delete",
            'affected_table' => "centers",
            'affected_record_id' => $center->id,
        ]);

        return response()->json([
            'message' => 'Centro eliminado correctamente.',
            'record' => $record,
        ]);
    }
}
