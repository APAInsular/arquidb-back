<?php

namespace App\Http\Controllers\Api\v1;

use Orion\Concerns\DisableAuthorization;
use Orion\Concerns\DisablePagination;
use Orion\Http\Controllers\Controller;
use App\Models\User;
use Orion\Http\Requests\Request as OrionRequest;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    use DisableAuthorization;

    protected $model = User::class;

    public function index(OrionRequest $request)
    {
        $name = $request->get('name');
        $page = $request->get('per_page');
        $all = $request->boolean('all', false);

        $query = User::orderBy('id', 'Asc')
            ->name($name)
            ->with('center', 'roles', 'permissions');

        $all ? $users = $query->get() : $users = $query->paginate($page ? $page : 10);

        return response()->json($users);

    }

    public function show(OrionRequest $request, ...$args)
    {
        $id = $args[0];

        $users = User::with('center', 'roles', 'roles.permissions')->findOrFail($id);
        return response()->json($users);

    }

}
