<?php

namespace App\Http\Controllers\Api\v1;

use App\Models\Collegiate;
use Orion\Concerns\DisableAuthorization;
use Orion\Concerns\DisablePagination;
use Orion\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CollegiateController extends Controller
{
    // prueba
    use DisableAuthorization, DisablePagination;
    protected $model = Collegiate::class;
}
