<?php

namespace App\Imports;

use App\Models\Person;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Illuminate\Support\Facades\Log;
use Throwable;

class PeopleImport implements ToModel, WithHeadingRow, SkipsOnError, WithBatchInserts
{
    protected $tracker;

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
            // 1. Validar y preparar datos
            $personData = $this->preparePersonData($row);

            if ($personData === null) {
                $this->tracker->incrementFailed();
                $this->tracker->incrementProcessed();
                return null;
            }

            // 2. Crear o actualizar persona
            $person = $this->savePerson($personData);

            $this->tracker->incrementProcessed();
            return $person;
        } catch (\Exception $e) {
            Log::error('Error procesando fila de persona: ' . $e->getMessage());
            $this->tracker->incrementFailed();
            $this->tracker->incrementProcessed();
            return null;
        }
    }

    protected function preparePersonData(array $row): ?array
    {
        // Validar campos obligatorios
        $requiredFields = [
            'nif' => $row['nif'] ?? $row['Nif'] ?? null,
            'nombre' => $row['nombre'] ?? $row['Nombre'] ?? null,
        ];

        foreach ($requiredFields as $field => $value) {
            if (empty(trim($value ?? ''))) {
                Log::warning("Campo requerido faltante: {$field}", $row);
                return null;
            }
        }

        // Determinar el tipo de identificación basado en el NIF
        $identificationType = $this->determineIdentificationType($row['nif']);

        return [
            'identification_type' => $identificationType,
            'identification_number' => trim($row['nif']),
            'name' => trim($row['nombre']),
            'first_surname' => isset($row['apellido1']) ? trim($row['apellido1']) : null,
            'second_surname' => isset($row['apellido2']) ? trim($row['apellido2']) : null,
        ];
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

    protected function savePerson(array $personData): ?Person
    {
        try {
            $person = Person::updateOrCreate(
                ['identification_number' => $personData['identification_number']],
                $personData
            );

            $this->tracker->incrementSuccessful();
            return $person;
        } catch (\Exception $e) {
            Log::error("Error guardando persona {$personData['identification_number']}: " . $e->getMessage());
            $this->tracker->incrementFailed();
            return null;
        }
    }

    public function onError(Throwable $e)
    {
        Log::error('Error en importación de personas: ' . $e->getMessage());
        $this->tracker->incrementFailed();
        $this->tracker->incrementProcessed();
    }
}
