<?php

namespace App\Http\Controllers\Api\v1;

use App\Models\Expedient;
use Orion\Concerns\DisableAuthorization;
use Orion\Concerns\DisablePagination;
use Orion\Http\Controllers\Controller;
use Orion\Http\Requests\Request as OrionRequest;
use Illuminate\Http\Request;

class ExpedientController extends Controller
{
    // prueba
    use DisableAuthorization;
    protected $model = Expedient::class;

    public function index(OrionRequest $request)
    {
        $title = $request->get('title');
        $phase = $request->get('phase');
        $client = $request->get('client');
        $collegiate = $request->get('collegiate');
        $dateCreated = $request->get('date');
        $all = $request->boolean('all', false);

        $query = Expedient::orderBy('id', 'Asc')
            ->title($title)
            ->phase($phase)
            ->client($client)
            ->collegiate($collegiate)
            ->dateCreated($dateCreated);

        $all ? $expedients = $query->get() : $expedients = $query->paginate(5);

        return response()->json($expedients);

    }
}
