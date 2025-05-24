<?php

namespace App\Http\Controllers\Api\v1;

use App\Models\Expedient;
use Illuminate\Http\Request;
use Orion\Concerns\DisableAuthorization;
use Orion\Concerns\DisablePagination;
use Orion\Http\Controllers\RelationController;

class ExpedientHasPeopleController extends RelationController
{
    use DisablePagination;
    use DisableAuthorization;

    protected $model = Expedient::class;

    protected $relation = 'people';

    public function assignPeople(Request $request, Expedient $expedient)
    {
        $validated = $request->validate([
            'people' => 'required|array',
            'people.*.id' => 'required|integer|exists:people,id',
            'people.*.role' => 'required|string|max:255',
        ]);

        // Preparamos los datos para sync
        $dataToSync = collect($validated['people'])->mapWithKeys(function ($person) {
            return [
                $person['id'] => ['role' => $person['role']],
            ];
        })->toArray();

        // Agrega sin eliminar relaciones anteriores
        $expedient->people()->syncWithoutDetaching($dataToSync);

        return response()->json(['message' => 'Personas asignadas al expediente correctamente.']);
    }
}
