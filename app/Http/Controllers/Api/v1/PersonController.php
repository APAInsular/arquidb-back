<?php

namespace App\Http\Controllers\Api\v1;

use App\Models\Person;
use Orion\Concerns\DisableAuthorization;
use Orion\Concerns\DisablePagination;
use Orion\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PersonController extends Controller
{
    use DisableAuthorization, DisablePagination;
    protected $model = Person::class;
}
