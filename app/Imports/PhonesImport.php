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

    public function __construct(MultiSheetImport $tracker)
    {
        $this->tracker = $tracker;
    }
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function batchSize(): int
    {
        return 1000; // Procesar 1000 filas a la vez
    }

    public function rules(): array
    {
        return [
            'phone' => 'required|char:9|unique',
        ];
    }

    public function customValidationMessages()
    {
        return [
            'phone.char:9' => 'Introduce un numero de 9 digitos',
        ];
    }

    public function onError(Throwable $e)
    {
        return response()->json($e);
    }

    public function model(array $row)
    {
        // Verifica que los datos se están leyendo correctamente
        Log::info('Processing phone row:', $row);

        $this->tracker->incrementProcessed();

        // return new Phone([
        //     'person_id' => 16,
        //     'phone' => $row['Telefonos'],
        // ]);
        try {
            $phone = new Phone([
                'person_id' => 16,
                'phone' => $row['Telefonos'],
            ]);

            $this->tracker->incrementSuccessful();
            return $phone;
        } catch (\Exception $e) {
            $this->tracker->incrementFailed();
            Log::error('Error importing phone: ' . $e->getMessage());
            return null;
        }
    }
}
