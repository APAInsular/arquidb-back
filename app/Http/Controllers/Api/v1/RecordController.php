<?php

namespace App\Http\Controllers\Api\v1;

use App\Models\Record;
use Orion\Concerns\DisableAuthorization;
use Orion\Concerns\DisablePagination;
use Orion\Http\Controllers\Controller;
use Orion\Http\Requests\Request as OrionRequest;
use Illuminate\Http\Request;

class RecordController extends Controller
{
    // use DisableAuthorization, DisablePagination;
    protected $model = Record::class;

    public function index(OrionRequest $request)
    {
        $all = $request->boolean('all', false);

        $query = Record::orderBy('created_at', 'desc');
        $all ? $records = $query->get() : $records = $query->paginate(20);

        return response()->json($records);
    }
}
