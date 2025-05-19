<?php

namespace App\Imports;

use App\Models\Person;
use App\Models\Expedient;
use App\Models\ExpedientHasPerson;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Illuminate\Support\Facades\Log;
use Throwable;

class ExpedientHasPeopleImport implements ToModel, WithHeadingRow, SkipsOnError, WithBatchInserts
{
    protected $tracker;
    protected $currentExpedientId = null;
    protected $currentPersonId = null;

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
            // 1. Validar y preparar datos
            $nif = $this->findNifColumn($row);

            if (empty($nif)) {
                Log::warning('No se encontró NIF en fila', $row);
                $this->tracker->incrementFailed();
                $this->tracker->incrementProcessed();
                return null;
            }

            // 2. Buscar persona
            $person = Person::where('identification_number', $nif)->first();

            if (!$person) {
                Log::warning("No se encontró persona con NIF: {$nif}");
                $this->tracker->incrementFailed();
                $this->tracker->incrementProcessed();
                return null;
            }
            $this->currentPersonId = $person->id;

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

            $this->saveExpedientPerson([
                'expedient_id' => $this->currentExpedientId,
                'person_id' => $this->currentPersonId,
            ]);

            $this->tracker->incrementProcessed();
            return $person;
        } catch (\Exception $e) {
            Log::error('Error procesando fila de persona: ' . $e->getMessage());
            $this->tracker->incrementFailed();
            $this->tracker->incrementProcessed();
            return null;
        }
    }

    protected function findNifColumn(array $row): ?string
    {
        $possibleColumns = ['nifcliente', 'idcolegiado', 'NIF', 'nif'];

        foreach ($possibleColumns as $column) {
            if (isset($row[$column]) && !empty(trim($row[$column]))) {
                return trim($row[$column]);
            }
        }

        return null;
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

    protected function determineIdentificationType(string $nif): string
    {
        $nif = strtoupper(trim($nif));

        // Lógica para determinar el tipo de documento
        if (preg_match('/^[A-Z]\d{7}[A-Z0-9]$/', $nif)) {
            return 'NIF'; // Código de identificación fiscal para empresas
        } elseif (preg_match('/^[XYZ]\d{7}[A-Z]$/', $nif)) {
            return 'NIE'; // Número de identificación de extranjero
        } elseif (preg_match('/^\d{8}[A-Z]$/', $nif)) {
            return 'DNI'; // Documento nacional de identidad
        } else {
            return 'OTRO'; // Otro tipo de identificación
        }
    }

    protected function saveExpedientPerson(array $expedientPersonData): ?ExpedientHasPerson
    {
        try {
            $person = ExpedientHasPerson::updateOrCreate(
                [
                    'expedient_id' => $expedientPersonData['expedient_id'],
                    'person_id' => $expedientPersonData['person_id'],
                ],
                $expedientPersonData
            );

            $this->tracker->incrementSuccessful();
            return $person;
        } catch (\Exception $e) {
            Log::error("Error guardando relación {$expedientPersonData['expedient_id']}: " . $e->getMessage());
            $this->tracker->incrementFailed();
            return null;
        }
    }

    public function onError(Throwable $e)
    {
        Log::error('Error en importación de relaciones: ' . $e->getMessage());
        $this->tracker->incrementFailed();
        $this->tracker->incrementProcessed();
    }
}
