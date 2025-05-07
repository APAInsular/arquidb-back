<?php

namespace App\Http\Controllers\Api\v1;

use App\Models\Client;
use App\Models\Expedient;
use Orion\Concerns\DisableAuthorization;
use Orion\Concerns\DisablePagination;
use Orion\Http\Controllers\Controller;
use Orion\Http\Requests\Request as OrionRequest;
use Illuminate\Http\Request;

class ExpedientController extends Controller
{
    use DisableAuthorization;
    protected $model = Expedient::class;

    public function index(OrionRequest $request)
    {
        $number = $request->get('number');
        $title = $request->get('title');
        $phase = $request->get('phase');
        $client = $request->get('client');
        $collegiate = $request->get('collegiate');
        $dateFrom = $request->get('dateFrom');
        $dateTo = $request->get('dateTo');
        $page = $request->get('per_page');
        $all = $request->boolean('all', false);

        $query = Expedient::orderBy('id', 'Asc')
            ->number($number)
            ->title($title)
            ->phase($phase)
            ->client($client)
            ->collegiate($collegiate)
            ->dateFrom($dateFrom)
            ->dateTo($dateTo);

        $query->with('people.clients', 'people.collegiates', 'phases.documents');
        $all ? $expedients = $query->get() : $expedients = $query->paginate($page ? $page : 5);

        return response()->json($expedients);

    }
}
