<?php

namespace App\Imports;

use App\Models\Phone;
use Maatwebsite\Excel\Concerns\ToModel;

class PhonesImport implements ToModel
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        return new Phone([
            //
        ]);
    }
}
