<?php

namespace App\Imports;

use App\Models\Expedient;
use Maatwebsite\Excel\Concerns\ToModel;

class ExpedientsImport implements ToModel
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        return new Expedient([
            //
        ]);
    }
}
