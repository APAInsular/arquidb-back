<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Requests\CollegiateRequest;
use App\Models\Collegiate;
use Orion\Concerns\DisableAuthorization;
use Orion\Concerns\DisablePagination;
use Orion\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CollegiateController extends Controller
{
    // prueba
    // use DisableAuthorization, DisablePagination;
    protected $model = Collegiate::class;

    // protected $request = CollegiateRequest::class;

    public function count(Request $request)
    {
        $query = Collegiate::orderBy('id', 'Asc');
        $collegiates = $query->get();

        $collegiateCount = $collegiates->count();

        return response()->json([
            'collegiates_account' => $collegiateCount,
        ]);
    }
}
