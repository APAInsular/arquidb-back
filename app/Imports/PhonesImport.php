<?php

namespace App\Imports;

use App\Models\Phone;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Illuminate\Support\Facades\Log;
use Throwable;

class PhonesImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnError, WithBatchInserts
{
    protected $tracker;
    protected $personId;

    public function __construct(MultiSheetImport $tracker, $personId = null)
    {
        $this->tracker = $tracker;
        $this->personId = $personId ?? 16; // Valor por defecto
    }

    public function batchSize(): int
    {
        return 1000;
    }

    public function rules(): array
    {
        return [
            'Telefonos' => 'required|digits:9', // Cambiado a digits:9
            // Si necesitas que sea único, considera usar:
            // 'Telefonos' => 'required|digits:9|unique:phones,phone'
        ];
    }

    public function customValidationMessages()
    {
        return [
            'Telefonos.digits' => 'El teléfono debe tener exactamente 9 dígitos',
            'Telefonos.required' => 'El campo teléfono es requerido',
        ];
    }

    public function model(array $row)
    {
        $phoneNumbers = $this->extractPhoneNumbers($row['Telefonos'] ?? $row[18] ?? '');

        if (empty($phoneNumbers)) {
            Log::warning('No valid phone numbers found in row: ', $row);
            return null;
        }

        $models = [];
        foreach ($phoneNumbers as $number) {
            try {
                $models[] = new Phone([
                    'person_id' => $this->personId,
                    'phone' => $number,
                ]);
                $this->tracker->incrementSuccessful();
            } catch (\Exception $e) {
                $this->tracker->incrementFailed();
                Log::error("Error saving phone {$number}: " . $e->getMessage());
            }
        }

        $this->tracker->incrementProcessed();

        // Laravel Excel espera que devolvamos un solo modelo o null
        // Devolvemos el último modelo creado o null si no hubo ninguno
        return !empty($models) ? end($models) : null;
    }

    protected function extractPhoneNumbers(string $phoneString): array
    {
        // Separar por guiones, comas o puntos y coma
        $rawNumbers = preg_split('/[-,\;\s]+/', $phoneString);

        $validNumbers = [];
        foreach ($rawNumbers as $number) {
            $cleanNumber = $this->cleanPhoneNumber($number);
            if ($this->isValidPhone($cleanNumber)) {
                $validNumbers[] = $cleanNumber;
            }
        }

        return $validNumbers;
    }

    protected function cleanPhoneNumber(string $number): string
    {
        // Eliminar espacios y caracteres no numéricos
        return preg_replace('/[^0-9]/', '', $number);
    }

    protected function isValidPhone(string $number): bool
    {
        // Validar que tenga exactamente 9 dígitos
        return strlen($number) === 9 &&
            ctype_digit($number) &&
            in_array(substr($number, 0, 1), ['9', '6', '7']);
    }

    public function onError(Throwable $e)
    {
        Log::error('Import error: ' . $e->getMessage());
        $this->tracker->incrementFailed();
    }
}
