<?php

namespace App\Imports;

use App\Models\Client;
use App\Models\Person;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Illuminate\Support\Facades\Log;
use Throwable;
use Illuminate\Support\Facades\Validator;

class ClientsImport implements ToModel, WithHeadingRow, SkipsOnError, WithBatchInserts
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

            // 3. Validar datos específicos de cliente
            $clientData = $this->prepareClientData($row);

            if (!$clientData) {
                $this->tracker->incrementFailed();
                $this->tracker->incrementProcessed();
                return null;
            }

            // 4. Crear o actualizar el cliente
            $this->saveClient($clientData);

            $this->tracker->incrementProcessed();
            return null;
        } catch (\Exception $e) {
            Log::error('Error procesando fila de cliente: ' . $e->getMessage());
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

    protected function prepareClientData(array $row): ?array
    {
        // Validar campos requeridos para cliente
        // $requiredFields = [
        //     'codigo_cliente' => $row['codigo_cliente'] ?? $row['codigo'] ?? null,
        //     'tipo_cliente' => $row['tipo_cliente'] ?? $row['tipo'] ?? null
        // ];

        // foreach ($requiredFields as $field => $value) {
        //     if (empty(trim($value ?? ''))) {
        //         Log::warning("Campo requerido faltante para cliente: {$field}", $row);
        //         return null;
        //     }
        // }

        return [
            'person_id' => $this->currentPersonId,
        ];
    }

    protected function saveClient(array $clientData): void
    {
        try {
            $validator = Validator::make($clientData, [
                'person_id' => 'required|exists:people,id',
                'agent' => 'nullable|string|max:50',
            ]);

            if ($validator->fails()) {
                throw new \Exception($validator->errors()->first());
            }

            // Usamos updateOrCreate porque el código de cliente podría necesitar actualización
            Client::updateOrCreate(
                [
                    'person_id' => $clientData['person_id']
                ],
                $clientData
            );

            $this->tracker->incrementSuccessful();
        } catch (\Exception $e) {
            Log::error("Error guardando cliente: " . $e->getMessage());
            $this->tracker->incrementFailed();
        }
    }

    public function onError(Throwable $e)
    {
        Log::error('Error en importación de clientes: ' . $e->getMessage());
        $this->tracker->incrementFailed();
        $this->tracker->incrementProcessed();
    }
}
