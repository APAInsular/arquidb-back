<?php

namespace App\Http\Controllers\Api\v1;

use App\Models\Phase;
use Orion\Concerns\DisableAuthorization;
use Orion\Concerns\DisablePagination;
use Orion\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PhaseController extends Controller
{
    use DisableAuthorization, DisablePagination;
    protected $model = Phase::class;
}
