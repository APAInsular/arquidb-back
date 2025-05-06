<?php

namespace App\Imports;

use App\Models\Phase;
use App\Models\Expedient;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Illuminate\Support\Facades\Log;
use Throwable;
use Illuminate\Support\Facades\Validator;

class PhasesImport implements ToModel, WithHeadingRow, SkipsOnError, WithBatchInserts
{
    protected $tracker;
    protected $currentExpedientId = null;

    public function __construct(MultiSheetImport $tracker)
    {
        $this->tracker = $tracker;
    }

    public function batchSize(): int
    {
        return 100;
    }

    public function model(array $row)
    {
        try {
            // 1. Buscar el número de expediente en la fila
            $expedientNumber = $this->findExpedientColumn($row); // Error tipográfico corregido

            if (empty($expedientNumber)) {
                Log::warning('No se encontró número de expediente en fila', $row);
                $this->tracker->incrementFailed();
                $this->tracker->incrementProcessed();
                return null;
            }

            // 2. Buscar el expediente en la base de datos por el campo number
            $expedient = Expedient::where('number', $expedientNumber)->first();

            if (!$expedient) {
                Log::warning("No se encontró expediente con número: {$expedientNumber}");
                $this->tracker->incrementFailed();
                $this->tracker->incrementProcessed();
                return null;
            }

            $this->currentExpedientId = $expedient->id;

            // Resto del código permanece igual...
            $phaseData = $this->extractPhaseData($row);

            if (empty($phaseData['phase'])) {
                Log::warning('No se encontró tipo de fase para expediente: ' . $expedientNumber, $row);
                $this->tracker->incrementFailed();
                $this->tracker->incrementProcessed();
                return null;
            }

            $this->savePhase($phaseData);
            $this->tracker->incrementProcessed();

            return null;
        } catch (\Exception $e) {
            Log::error('Error procesando fila: ' . $e->getMessage(), ['row' => $row]);
            $this->tracker->incrementFailed();
            $this->tracker->incrementProcessed();
            return null;
        }
    }

    protected function findExpedientColumn(array $row): ?string
    {
        $possibleColumns = [
            'idexpedientefue',
            'number',
            'expedientnumber',
            'numero_expediente',
            'exp_number',
            'expediente',
            'num_expediente'
        ];

        foreach ($possibleColumns as $column) {
            if (isset($row[$column]) && !empty(trim($row[$column]))) {
                return trim($row[$column]);
            }
        }

        return null;
    }

    protected function extractPhaseData(array $row): array
    {
        $phaseType = $this->findPhaseTypeColumn($row);
        $phaseTitle = $this->findPhaseTitleColumn($row);

        return [
            'phase' => $phaseType,
            'title' => $phaseTitle,
            'observations' => null,
            'state' => 'unsigned',
            'sign_date' => null,
            'start_date' => $row['fechainicio'],
            'record_date' => $row['fecharegistro'],
            'expedient_id' => $this->currentExpedientId
        ];
    }

    protected function findPhaseTypeColumn(array $row): ?string
    {
        $possibleColumns = [
            'idtipofase',
            'tipo_fase',
            'phase_type',
            'phase',
            'fase',
            'tipo_fase_id'
        ];

        foreach ($possibleColumns as $column) {
            if (isset($row[$column]) && !empty(trim($row[$column]))) {
                return trim($row[$column]);
            }
        }

        return null;
    }

    protected function findPhaseTitleColumn(array $row): ?string
    {
        $possibleColumns = [
            'tipo_trabajo',
            'title',
            'titulo',
            'nombre_fase',
            'phase_title',
            'descripcion',
            'description'
        ];

        foreach ($possibleColumns as $column) {
            if (isset($row[$column]) && !empty(trim($row[$column]))) {
                return trim($row[$column]);
            }
        }

        return null;
    }

    protected function savePhase(array $phaseData): void
    {
        try {
            $validator = Validator::make($phaseData, [
                'phase' => [
                    'required',
                    'string',
                    'max:4',
                    'regex:/^\d{3,4}$/',
                    function ($attribute, $value, $fail) {
                        if ($value < '000' || $value > '9999') {
                            $fail('El campo phase debe estar entre 000 y 9999.');
                        }
                    }
                ],
                'title' => 'nullable|string|max:255',
                'expedient_id' => 'required|exists:expedients,id',
                'sign_date' => 'required|date',
                'start_date' => 'required|date',
                'record_date' => 'required|date',
            ]);

            if ($validator->fails()) {
                throw new \Exception($validator->errors()->first());
            }

            // Verificar si esta fase ya existe para el expediente
            $exists = Phase::where('phase', $phaseData['phase'])
                ->where('expedient_id', $phaseData['expedient_id'])
                ->exists();

            if (!$exists) {
                Phase::create($phaseData);
                $this->tracker->incrementSuccessful();
                Log::info("Fase creada para expediente {$phaseData['expedient_id']}: {$phaseData['phase']}");
            } else {
                Log::info("Fase {$phaseData['phase']} ya existe para expediente {$phaseData['expedient_id']} - omitiendo");
            }
        } catch (\Exception $e) {
            Log::error("Error guardando fase: " . $e->getMessage(), $phaseData);
            $this->tracker->incrementFailed();
        }
    }

    public function onError(Throwable $e)
    {
        Log::error('Error en importación de fases: ' . $e->getMessage());
        $this->tracker->incrementFailed();
        $this->tracker->incrementProcessed();
    }
}
