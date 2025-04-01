<?php

namespace App\Http\Controllers\Api\v1;

use App\Models\Expedient;
use Orion\Concerns\DisableAuthorization;
use Orion\Concerns\DisablePagination;
use Orion\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ExpedientController extends Controller
{
    // prueba
    use DisableAuthorization, DisablePagination;
    protected $model = Expedient::class;
}
