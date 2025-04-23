<?php

namespace App\Imports;

use App\Models\Phone;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Throwable;

class PhonesImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnError, WithBatchInserts
{
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
        return new Phone([
            'person_id' => 16,
            'phone' => $row['Telefonos'],
        ]);
    }
}
