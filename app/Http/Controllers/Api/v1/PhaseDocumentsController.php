<?php

namespace App\Http\Controllers\Api\v1;

use App\Models\Phase;
use Illuminate\Http\Request;
use Orion\Concerns\DisableAuthorization;
use Orion\Concerns\DisablePagination;
use Orion\Http\Controllers\RelationController;

class PhaseDocumentsController extends RelationController
{
    //
    use DisablePagination;
    use DisableAuthorization;

    protected $model = Phase::class;

    protected $relation = 'documents';
}
