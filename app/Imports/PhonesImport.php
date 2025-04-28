<?php

namespace App\Imports;

use App\Models\Phone;
use App\Models\Person;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Illuminate\Support\Facades\Log;
use Throwable;

class PhonesImport implements ToModel, WithHeadingRow, SkipsOnError, WithBatchInserts
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

            // 3. Procesar los teléfonos
            $phoneString = $this->findPhoneColumn($row);

            if (empty($phoneString)) {
                Log::warning('No se encontraron teléfonos para persona: ' . $nif, $row);
                $this->tracker->incrementFailed();
                $this->tracker->incrementProcessed();
                return null;
            }

            $phoneNumbers = $this->extractPhoneNumbers($phoneString);

            if (empty($phoneNumbers)) {
                Log::warning('No se encontraron números válidos para persona: ' . $nif);
                $this->tracker->incrementFailed();
                $this->tracker->incrementProcessed();
                return null;
            }

            // 4. Guardar cada número válido
            foreach ($phoneNumbers as $number) {
                $this->savePhoneNumber($number);
            }

            $this->tracker->incrementProcessed();
            return null;
        } catch (\Exception $e) {
            Log::error('Error procesando fila: ' . $e->getMessage());
            $this->tracker->incrementFailed();
            $this->tracker->incrementProcessed();
            return null;
        }
    }

    protected function findNifColumn(array $row): ?string
    {
        $possibleColumns = ['nif', 'Nif', 'NIF', 'identification_number'];

        foreach ($possibleColumns as $column) {
            if (isset($row[$column]) && !empty(trim($row[$column]))) {
                return trim($row[$column]);
            }
        }

        return null;
    }

    protected function findPhoneColumn(array $row): string
    {
        // (Mantener la misma implementación que ya tenías)
        $possibleColumns = ['Telefonos', 'telefonos', 'Teléfonos', 'teléfonos', 18];

        foreach ($possibleColumns as $column) {
            if (isset($row[$column]) && !empty(trim($row[$column]))) {
                Log::debug("Teléfonos encontrados en columna: {$column}");
                return trim($row[$column]);
            }
        }

        // Buscar cualquier columna que contenga números
        foreach ($row as $key => $value) {
            if (is_string($value) && preg_match('/\d{7,}/', $value)) {
                Log::info("Teléfonos encontrados en columna no estándar: {$key}");
                return trim($value);
            }
        }

        return '';
    }

    protected function extractPhoneNumbers(string $phoneString): array
    {
        // (Mantener la misma implementación que ya tenías)
        $rawNumbers = preg_split('/[-,\;\s\/]+/', $phoneString);

        $validNumbers = [];
        foreach ($rawNumbers as $number) {
            $cleanNumber = preg_replace('/[^0-9]/', '', $number);

            if ($this->isValidPhone($cleanNumber)) {
                $validNumbers[] = $cleanNumber;
            } elseif (!empty($cleanNumber)) {
                Log::warning("Número descartado (formato inválido): {$cleanNumber}");
            }
        }

        return $validNumbers;
    }

    protected function isValidPhone(string $number): bool
    {
        // (Mantener la misma implementación que ya tenías)
        return strlen($number) >= 7 && ctype_digit($number);
    }

    protected function savePhoneNumber(string $number): void
    {
        try {
            Phone::updateOrCreate(
                [
                    'phone' => $number,
                    'person_id' => $this->currentPersonId
                ],
                [
                    'phone' => $number,
                    'person_id' => $this->currentPersonId
                ]
            );
            $this->tracker->incrementSuccessful();
        } catch (\Exception $e) {
            Log::error("Error guardando teléfono {$number}: " . $e->getMessage());
            $this->tracker->incrementFailed();
        }
    }

    public function onError(Throwable $e)
    {
        Log::error('Error en importación: ' . $e->getMessage());
        $this->tracker->incrementFailed();
        $this->tracker->incrementProcessed();
    }
}
