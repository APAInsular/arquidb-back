<?php

namespace App\Imports;

use App\Models\Phone;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Illuminate\Support\Facades\Log;
use Throwable;

class PhonesImport implements ToModel, WithHeadingRow, SkipsOnError, WithBatchInserts
{
    protected $tracker;
    protected $personId;

    public function __construct(MultiSheetImport $tracker, $personId = null)
    {
        $this->tracker = $tracker;
        $this->personId = $personId ?? 16;
    }

    public function batchSize(): int
    {
        return 1000;
    }

    public function model(array $row)
    {
        try {
            // 1. Encontrar la columna con los teléfonos
            $phoneString = $this->findPhoneColumn($row);

            if (empty($phoneString)) {
                Log::warning('No se encontró columna con teléfonos en fila', $row);
                $this->tracker->incrementFailed();
                return null;
            }

            // 2. Extraer y validar números
            $phoneNumbers = $this->extractPhoneNumbers($phoneString);

            if (empty($phoneNumbers)) {
                Log::warning('No se encontraron números válidos en: ' . $phoneString);
                $this->tracker->incrementFailed();
                return null;
            }

            // 3. Guardar cada número válido
            foreach ($phoneNumbers as $number) {
                $this->savePhoneNumber($number);
            }

            $this->tracker->incrementProcessed();
            return null;
        } catch (\Exception $e) {
            Log::error('Error procesando fila: ' . $e->getMessage());
            $this->tracker->incrementFailed();
            return null;
        }
    }

    protected function findPhoneColumn(array $row): string
    {
        // Buscar en posibles nombres de columnas
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
        // Separar por múltiples delimitadores
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
        // Validación más flexible para números internacionales
        return strlen($number) >= 7 && ctype_digit($number);
    }

    protected function savePhoneNumber(string $number): void
    {
        try {
            Phone::updateOrCreate(
                ['phone' => $number],
                ['person_id' => $this->personId]
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
    }
}
