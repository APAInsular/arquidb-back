<?php

namespace App\Imports;

use App\Models\Collegiate;
use Maatwebsite\Excel\Concerns\ToModel;

class CollegiatesImport implements ToModel
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        return new Collegiate([
            //
        ]);
    }
}
