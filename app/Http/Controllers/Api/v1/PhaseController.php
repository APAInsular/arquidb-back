<?php

namespace App\Http\Controllers\Api\v1;

use App\Models\Phase;
use Orion\Concerns\DisableAuthorization;
use Orion\Concerns\DisablePagination;
use Orion\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PhaseController extends Controller
{
    use DisableAuthorization, DisablePagination;
    protected $model = Phase::class;

    public function titles(Request $request)
    {
        $phaseMappings = [
            '911' => "Plan Parcial",
            '31'  => "Minuta A/C de Proyecto básico",
            '45'  => "Proyecto de ejecución - Proyectos parciales",
            '55'  => "Proyecto básico + Ejecución - Proyectos parciales",
            '62'  => "Libro de órdenes",
            '64'  => "Minutas A/C de Dirección de obras",
            '78'  => "Anexos a proyectos",
            '85'  => "Certificios",
            '92'  => "Plan General",
            '93'  => "Normas subsidiarias",
            '94'  => "Proyecto de urbanización",
            '95'  => "Plan especial",
            '96'  => "Informes",
            '97'  => "Varios urbanismo",
            '98'  => "Otros",
            '0'   => "Contrato o Comunicación de encargo",
            '1'   => "Estudios previos",
            '2'   => "Anteproyecto",
            '3'   => "Proyecto básico",
            '4'   => "Proyecto de ejecución",
            '5'   => "Proyecto básico + Ejecución",
            '6'   => "Certificado Parcial",
            '7'   => "Certificado final",
            '8'   => "Ampliación, Reformados y Acondicionamientos",
            '9'   => "Estudio de detalles"
        ];

        $expedientId = $request->expedientId;

        $newPhases = collect($request->expedientPhases)->map(function ($phase) use ($expedientId, $phaseMappings) {
            $title = '';

            // Buscar el mapeo más específico primero (prefijos de 3 dígitos)
            foreach ([3, 2, 1] as $length) {
                $prefix = substr($phase['phase'], 0, $length);
                if (isset($phaseMappings[$prefix])) {
                    $title = $phaseMappings[$prefix];
                    break;
                }
            }

            return array_merge($phase, [
                'title' => $title,
                'expedient_id' => $expedientId
            ]);
        });

        return response()->json($newPhases);
    }
}
