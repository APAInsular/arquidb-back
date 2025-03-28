<?php

namespace App\Http\Controllers\Api\v1;

use App\Models\Record;
use Orion\Concerns\DisableAuthorization;
use Orion\Concerns\DisablePagination;
use Orion\Http\Controllers\Controller;
use Illuminate\Http\Request;

class RecordController extends Controller
{
    use DisableAuthorization, DisablePagination;
    protected $model = Record::class;
}
