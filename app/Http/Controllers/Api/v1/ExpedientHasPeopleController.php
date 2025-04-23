<?php

namespace App\Http\Controllers\Api\v1;

use App\Models\Expedient;
use Illuminate\Http\Request;
use Orion\Concerns\DisableAuthorization;
use Orion\Concerns\DisablePagination;
use Orion\Http\Controllers\RelationController;

class ExpedientHasPeopleController extends RelationController
{
    use DisablePagination;
    use DisableAuthorization;

    protected $model = Expedient::class;

    protected $relation = 'people';
}
