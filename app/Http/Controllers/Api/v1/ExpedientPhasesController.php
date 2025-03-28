<?php

namespace App\Http\Controllers\Api\v1;

use App\Models\Expedient;
use Orion\Concerns\DisableAuthorization;
use Orion\Concerns\DisablePagination;
use Orion\Http\Controllers\RelationController;

class ExpedientPhasesController extends RelationController
{
    //
    use DisablePagination;
    use DisableAuthorization;

    protected $model = Expedient::class;

    protected $relation = 'phases';
}
