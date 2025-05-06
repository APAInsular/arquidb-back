<?php

namespace App\Imports;

use App\Models\Address;
use App\Models\Person;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Illuminate\Support\Facades\Log;
use Throwable;
use Illuminate\Support\Facades\Validator;

class AddressesImport implements ToModel, WithHeadingRow, SkipsOnError, WithBatchInserts
{
    protected $tracker;
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

            // 3. Preparar datos de la dirección
            $addressData = $this->prepareAddressData($row);

            if (!$addressData) {
                $this->tracker->incrementFailed();
                $this->tracker->incrementProcessed();
                return null;
            }

            // 4. Guardar la dirección
            $this->saveAddress($addressData);

            $this->tracker->incrementProcessed();
            return null;
        } catch (\Exception $e) {
            Log::error('Error procesando fila de dirección: ' . $e->getMessage());
            $this->tracker->incrementFailed();
            $this->tracker->incrementProcessed();
            return null;
        }
    }

    protected function findNifColumn(array $row): ?string
    {
        $possibleColumns = ['nif', 'Nif', 'NIF', 'identification_number', 'dni'];

        foreach ($possibleColumns as $column) {
            if (isset($row[$column]) && !empty(trim($row[$column]))) {
                return trim($row[$column]);
            }
        }

        return null;
    }

    protected function prepareAddressData(array $row): ?array
    {
        // Validar campos requeridos
        $requiredFields = [
            'direccion' => $row['direccion'] ?? $row['address'] ?? null,
            'idcodigopostal' => $row['idcodigopostal'] ?? $row['postal_code'] ?? null
        ];

        foreach ($requiredFields as $field => $value) {
            if (empty(trim($value ?? ''))) {
                Log::warning("Campo requerido faltante para dirección: {$field}", $row);
                return null;
            }
        }

        return [
            'person_id' => $this->currentPersonId,
            'street' => trim($row['direccion'] ?? $row['address']),
            'number' => trim($row['num'] ?? $row['number'] ?? 'S/N'),
            'municipality' => trim($row['municipio'] ?? $row['municipality'] ?? ''),
            'province' => trim($row['provincia'] ?? $row['province'] ?? ''),
            'postal_code' => trim($row['idcodigopostal'] ?? $row['postal_code']),
            'country' => trim($row['pais'] ?? $row['country'] ?? 'España'),
            'locality' => trim($row['localidad'] ?? $row['locality'] ?? '')
        ];
    }

    protected function saveAddress(array $addressData): void
    {
        try {
            $validator = Validator::make($addressData, [
                'person_id' => 'required|exists:people,id',
                'street' => 'required|string|max:255',
                'number' => 'required|numeric', // Changed to numeric validation
                'postal_code' => 'required|string|size:5', // Changed to exact size 5 for char(5)
                'country' => 'nullable|string|max:100',
                'province' => 'nullable|string|max:100',
                'municipality' => 'nullable|string|max:100',
                'locality' => 'nullable|string|max:100'
            ]);

            if ($validator->fails()) {
                throw new \Exception($validator->errors()->first());
            }

            Address::create($addressData);
            $this->tracker->incrementSuccessful();
        } catch (\Exception $e) {
            Log::error("Error guardando dirección: " . $e->getMessage());
            $this->tracker->incrementFailed();
        }
    }

    public function onError(Throwable $e)
    {
        Log::error('Error en importación de direcciones: ' . $e->getMessage());
        $this->tracker->incrementFailed();
        $this->tracker->incrementProcessed();
    }
}
