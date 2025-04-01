<?php

namespace App\Http\Controllers\Api\v1;

use App\Models\Client;
use Orion\Http\Controllers\Controller;
use Orion\Concerns\DisableAuthorization;
use Orion\Concerns\DisablePagination;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    // prueba
    use DisableAuthorization, DisablePagination;
    protected $model = Client::class;
}
