<?php

namespace App\Imports;

use App\Models\Document;
use Maatwebsite\Excel\Concerns\ToModel;

class DocumentsImport implements ToModel
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        return new Document([
            //
        ]);
    }
}
