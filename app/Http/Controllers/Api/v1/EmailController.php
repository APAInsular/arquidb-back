<?php

namespace App\Http\Controllers\Api\v1;

use App\Models\Email;
use Orion\Concerns\DisableAuthorization;
use Orion\Concerns\DisablePagination;
use Orion\Http\Controllers\Controller;
use Illuminate\Http\Request;

class EmailController extends Controller
{
    // prueba
    use DisableAuthorization, DisablePagination;
    protected $model = Email::class;
}
