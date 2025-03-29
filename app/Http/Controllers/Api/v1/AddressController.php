<?php

namespace App\Http\Controllers\Api\v1;

use App\Models\Address;
use Orion\Concerns\DisableAuthorization;
use Orion\Concerns\DisablePagination;
use Orion\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AddressController extends Controller
{
    // prueba
    use DisableAuthorization, DisablePagination;

    protected $model = Address::class;

}
