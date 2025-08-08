<?php

namespace App\Imports;

use App\Models\Expedient;
use App\Models\Center;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\Importable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Carbon;
use Throwable;

class ExpedientsImport implements ToModel, WithHeadingRow, SkipsOnError, WithBatchInserts
{
    use Importable;

    protected $tracker;

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
        static $count = 0;
        $count++;

        if ($count % 100 === 0) {
            gc_collect_cycles(); // Limpia memoria cada 100 filas
        }

        try {
            // 1. Preparar datos del expediente
            $expedientData = $this->prepareExpedientData($row);

            if ($expedientData === null) {
                $this->tracker->incrementFailed();
                $this->tracker->incrementProcessed();
                return null;
            }

            // 2. Validar y guardar el expediente
            $expedient = $this->saveExpedient($expedientData);

            $this->tracker->incrementProcessed();
            return $expedient;
        } catch (\Exception $e) {
            Log::error('Error procesando fila de expediente: ' . $e->getMessage());
            $this->tracker->incrementFailed();
            $this->tracker->incrementProcessed();
            return null;
        }
    }

    protected function prepareExpedientData(array $row): ?array
    {
        // Validar campos obligatorios
        $requiredFields = [
            'number' => $row['idexpedientefue'] ?? $row['nexpediente'] ?? $row['numero'] ?? null,
            'start_date' => $row['fecha_inicio'] ?? $row['start_date'] ?? null,
        ];

        foreach ($requiredFields as $field => $value) {
            if (empty(trim($value ?? ''))) {
                Log::warning("Campo requerido faltante: {$field}", $row);
                return null;
            }
        }

        // Buscar centro si no viene el ID directamente
        $centerId = $this->findCenterId($row);
        // 'postal_code' => trim($row['codigopostal'] ?? $row['postal_code'] ?? ''),
        return [
            'title' => trim($row['descripcion'] ?? $row['title'] ?? $row['titulo'] ?? ''),
            'number' => trim($row['idexpedientefue'] ?? $row['nexpediente'] ?? $row['numero']),
            'start_date' => $this->parseDate($row['fecha_inicio'] ?? $row['start_date']),
            'end_date' => isset($row['fecha_fin']) ? $this->parseDate($row['fecha_fin']) : null,
            'description' => trim($row['descripcion'] ?? $row['description'] ?? ''),
            'site' => trim($row['emplazamiento'] ?? $row['site'] ?? $row['lugar'] ?? ''),
            'postal_code' => trim($row['codigopostal'] ?? '35600'),
            'budget' => $this->parseBudget($row['presupuesto'] ?? $row['budget'] ?? 0),
            'center_id' => $centerId,
        ];
    }

    protected function findCenterId(array $row): int
    {
        // Si viene directamente el ID del centro
        if (isset($row['center_id']) && is_numeric($row['center_id'])) {
            return (int)$row['center_id'];
        }

        // Buscar por código o nombre si no viene el ID
        $centerIdentifier = $row['centro'] ?? $row['center'] ?? $row['codigo_centro'] ?? null;

        if ($centerIdentifier) {
            $center = Center::where('code', $centerIdentifier)
                ->orWhere('name', $centerIdentifier)
                ->first();

            if ($center) {
                return $center->id;
            }
        }

        // Valor por defecto (1)
        return 1;
    }

    protected function parseDate($date)
    {
        if (empty($date)) {
            return null;
        }

        try {
            if (is_numeric($date)) {
                // Para fechas en formato Excel (días desde 1900)
                $dateTime = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($date);
                return Carbon::instance($dateTime);
            }

            // Si es texto en formato como 5/12/1972
            return Carbon::createFromFormat('d/m/Y', $date);
        } catch (\Exception $e) {
            Log::warning("Fecha no válida: {$date}");
            return null;
        }
    }

    protected function parseBudget($value): float
    {
        if (is_numeric($value)) {
            return (float)$value;
        }

        // Limpiar formato de moneda (€, $, etc.)
        $cleaned = preg_replace('/[^0-9.,]/', '', $value);
        $cleaned = str_replace(',', '.', $cleaned);

        return (float)$cleaned;
    }

    protected function saveExpedient(array $expedientData): ?Expedient
    {
        try {
            $validator = Validator::make($expedientData, [
                'title' => 'nullable|string|max:255',
                'number' => 'required|string|max:50',
                'start_date' => 'required|date',
                'end_date' => 'nullable|date',
                'description' => 'nullable|string',
                'site' => 'nullable|string|max:100',
                'postal_code' => 'nullable|string|max:10',
                'budget' => 'nullable|numeric',
                'center_id' => 'required|exists:centers,id',
            ]);

            if ($validator->fails()) {
                throw new \Exception($validator->errors()->first());
            }

            $expedient = Expedient::updateOrCreate(
                ['number' => $expedientData['number']],
                $expedientData
            );

            $this->tracker->incrementSuccessful();
            return $expedient;
        } catch (\Exception $e) {
            Log::error("Error guardando expediente {$expedientData['number']}: " . $e->getMessage());
            $this->tracker->incrementFailed();
            return null;
        }
    }

    public function onError(Throwable $e)
    {
        Log::error('Error en importación de expedientes: ' . $e->getMessage());
        $this->tracker->incrementFailed();
        $this->tracker->incrementProcessed();
    }
}
