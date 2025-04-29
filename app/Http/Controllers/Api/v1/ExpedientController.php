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
    // prueba
    use DisableAuthorization;
    protected $model = Expedient::class;

    public function index(OrionRequest $request)
    {
        $number = $request->get('number');
        $title = $request->get('title');
        $phase = $request->get('phase');
        $client = $request->get('client');
        $collegiate = $request->get('collegiate');
        $dateCreated = $request->get('date');
        $all = $request->boolean('all', false);

        $query = Expedient::orderBy('id', 'Asc')
            ->number($number)
            ->title($title)
            ->phase($phase)
            ->client($client)
            ->collegiate($collegiate)
            ->dateCreated($dateCreated);

        $query->with('people.clients', 'people.collegiates', 'phases.documents');

        $all ?
            $expedients = $query->get()
            :
            $expedients = $query->paginate(5);

        // dd();

        return response()->json($expedients);

    }
}
