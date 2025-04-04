<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\Collegiate;
use App\Models\Person;
use Illuminate\Http\Request;
use Orion\Concerns\DisableAuthorization;
use Orion\Concerns\DisablePagination;
use Orion\Http\Controllers\RelationController;
use View;

class PersonCollegiatesController extends RelationController
{
    //
    use DisablePagination;
    use DisableAuthorization;

    protected $model = Person::class;

    protected $relation = 'collegiates';

    public function Personcollegiate($id)
    {
        $person = Person::findOrFail($id);

        $collegiate = Collegiate::where('person_id', $id)->get();

        if ($collegiate) {
            return response()->json([
                'person' => $person,
                'collegiate' => $collegiate
            ]); 
        } else {
            return response()->json([
                'message' => 'No collegiate found for this person.'
            ], 404);
        }
    }
}
