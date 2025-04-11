<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Requests\PersonRequest;
use App\Models\Person;
use Orion\Concerns\DisableAuthorization;
use Orion\Concerns\DisablePagination;
use Orion\Http\Controllers\Controller;

class PersonController extends Controller
{
    use DisableAuthorization, DisablePagination;
    protected $model = Person::class;
    protected $request = PersonRequest::class;

}
