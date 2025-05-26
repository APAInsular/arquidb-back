<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\CenterRequest;
use App\Models\Center;
use Gate;
use Orion\Concerns\DisableAuthorization;
use Orion\Concerns\DisablePagination;
// use Orion\Http\Controllers\Controller;
use Orion\Http\Requests\Request as OrionRequest;
use Illuminate\Http\Request;

class CenterController extends Controller
{
    // prueba
    use DisableAuthorization, DisablePagination;

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

        return response()->json([
            'message' => 'Centro creado correctamente.',
            'data' => $center
        ], 201);
    }

    public function update(CenterRequest $request, $id)
    {
        $center = Center::findOrFail($id);
        Gate::authorize('update', $center);
        $validated = $request->validated();
        $center->update($validated);

        return response()->json([
            'message' => 'Centro actualizado correctamente.',
            'data' => $center
        ]);
    }

    public function destroy($id)
    {
        $center = Center::findOrFail($id);
        Gate::authorize('delete', $center);

        $center->delete();

        return response()->json([
            'message' => 'Centro eliminado correctamente.'
        ]);
    }
}
