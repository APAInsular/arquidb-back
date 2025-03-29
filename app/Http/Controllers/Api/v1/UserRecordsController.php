<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Orion\Concerns\DisableAuthorization;
use Orion\Concerns\DisablePagination;
use Orion\Http\Controllers\RelationController;

class UserRecordsController extends RelationController
{
    //
    use DisablePagination;
    use DisableAuthorization;

    protected $model = User::class;

    protected $relation = 'records';
}
