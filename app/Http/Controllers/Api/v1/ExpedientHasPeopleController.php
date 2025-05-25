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

        // Si el array está vacío, borra todas las relaciones
        if (empty($validated['people'])) {
            $expedient->people()->sync([]);
            return response()->json(['message' => 'Todas las personas han sido desvinculadas del expediente.']);
        }

        // Preparamos los datos para sync
        $dataToSync = collect($validated['people'])->mapWithKeys(function ($person) {
            return [
                $person['id'] => ['role' => $person['role']],
            ];
        })->toArray();

        // Reemplaza todas las relaciones anteriores por las nuevas
        $expedient->people()->sync($dataToSync);

        return response()->json(['message' => 'Personas asignadas al expediente correctamente.']);
    }
}
