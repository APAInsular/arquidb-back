<?php

namespace App\Imports;

use App\Models\Collegiate;
use App\Models\Person;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Illuminate\Support\Facades\Log;
use Throwable;
use Illuminate\Support\Facades\Validator;

class CollegiatesImport implements ToModel, WithHeadingRow, SkipsOnError, WithBatchInserts
{
    protected $tracker;
    protected $currentPersonId = null;

    public function __construct(MultiSheetImport $tracker)
    {
        $this->tracker = $tracker;
    }

    public function batchSize(): int
    {
        return 1000;
    }

    public function model(array $row)
    {
        try {
            // 1. Buscar el NIF en la fila para encontrar a la persona
            $nif = $this->findNifColumn($row);

            if (empty($nif)) {
                Log::warning('No se encontró NIF en fila', $row);
                $this->tracker->incrementFailed();
                $this->tracker->incrementProcessed();
                return null;
            }

            // 2. Buscar la persona en la base de datos
            $person = Person::where('identification_number', $nif)->first();

            if (!$person) {
                Log::warning("No se encontró persona con NIF: {$nif}");
                $this->tracker->incrementFailed();
                $this->tracker->incrementProcessed();
                return null;
            }

            $this->currentPersonId = $person->id;

            // 3. Validar datos específicos de colegiado
            $collegiateData = $this->prepareCollegiateData($row);

            if (!$collegiateData) {
                $this->tracker->incrementFailed();
                $this->tracker->incrementProcessed();
                return null;
            }

            // 4. Crear o actualizar el colegiado
            $this->saveCollegiate($collegiateData);

            $this->tracker->incrementProcessed();
            return null;
        } catch (\Exception $e) {
            Log::error('Error procesando fila de colegiado: ' . $e->getMessage());
            $this->tracker->incrementFailed();
            $this->tracker->incrementProcessed();
            return null;
        }
    }

    protected function findNifColumn(array $row): ?string
    {
        $possibleColumns = ['nif', 'Nif', 'NIF', 'idcolegiado', 'nifcolegiado', 'identificacion'];

        foreach ($possibleColumns as $column) {
            if (isset($row[$column]) && !empty(trim($row[$column]))) {
                return trim($row[$column]);
            }
        }

        return null;
    }

    protected function prepareCollegiateData(array $row): ?array
    {
        // $requiredFields = [
        //     'numero_colegiado' => $row['numero_colegiado'] ?? $row['colegiado'] ?? $row['num_colegiado'] ?? null,
        //     'colegio_profesional' => $row['colegio_profesional'] ?? $row['colegio'] ?? null
        // ];

        // foreach ($requiredFields as $field => $value) {
        //     if (empty(trim($value ?? ''))) {
        //         Log::warning("Campo requerido faltante para colegiado: {$field}", $row);
        //         return null;
        //     }
        // }

        return [
            'person_id' => $this->currentPersonId,
            // 'college_number' => $requiredFields['numero_colegiado'],
            // 'professional_college' => $requiredFields['colegio_profesional'],
            // 'specialty' => $row['especialidad'] ?? $row['specialty'] ?? null,
            // 'status' => $row['estado'] ?? $row['status'] ?? 'active',
            // 'registration_date' => $row['fecha_alta'] ?? $row['registration_date'] ?? null,
        ];
    }

    protected function saveCollegiate(array $collegiateData): void
    {
        try {
            $validator = Validator::make($collegiateData, [
                'person_id' => 'required|exists:people,id',
                // 'college_number' => 'required|string|max:50|unique:collegiates,college_number,' . ($collegiateData['id'] ?? 'NULL') . ',id,person_id,' . $collegiateData['person_id'],
                // 'professional_college' => 'required|string|max:255',
                // 'specialty' => 'nullable|string|max:255',
                // 'status' => 'required|string|in:active,inactive,suspended,retired',
                // 'registration_date' => 'nullable|date',
            ]);

            if ($validator->fails()) {
                throw new \Exception($validator->errors()->first());
            }

            Collegiate::updateOrCreate(
                [
                    'person_id' => $collegiateData['person_id'],
                    // 'college_number' => $collegiateData['college_number'],
                ],
                $collegiateData
            );

            $this->tracker->incrementSuccessful();
        } catch (\Exception $e) {
            Log::error("Error guardando colegiado: " . $e->getMessage());
            $this->tracker->incrementFailed();
        }
    }

    public function onError(Throwable $e)
    {
        Log::error('Error en importación de colegiados: ' . $e->getMessage());
        $this->tracker->incrementFailed();
        $this->tracker->incrementProcessed();
    }
}
