<?php

namespace App\Http\Controllers\Api\v1;

use App\Models\Record;
use Auth;
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
    // use DisableAuthorization;   

    protected $model = User::class;

    public function store(OrionRequest $request, ...$args)
    {

        $auth = $request->user();

        $data = $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:6',
            'role' => 'required|string|exists:roles,name',
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => bcrypt($data['password']),
            'center_id' => $auth->center_id,
        ]);

        $record = Record::create([
            'user_id' => $request->user()->id,
            'name' => $request->user()->name,
            'action' => "sign",
            'affected_table' => "users",
            'affected_record_id' => $user->id,
        ]);


        $user->assignRole($data['role']);

        return response()->json([
            'message' => 'Persona creada correctamente',
            'user' => $user,
            'record' => $record
        ]);
    }

    public function update(OrionRequest $request, ...$args)
    {
        $id = $args[0];

        $data = $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email,' . $id,
            'password' => 'nullable|string|min:6',
            'role' => 'required|string|exists:roles,name',
        ]);

        $user = User::findOrFail($id);

        $user->name = $data['name'];
        $user->email = $data['email'];
        if (!empty($data['password'])) {
            $user->password = bcrypt($data['password']);
        }
        $user->save();

        $record = Record::create([
            'user_id' => $request->user()->id,
            'name' => $request->user()->name,
            'action' => "update",
            'affected_table' => "users",
            'affected_record_id' => $user->id,
        ]);

        $user->syncRoles([$data['role']]);

        return response()->json([
            'message' => 'Usuario actualizado correctamente',
            'user' => $user,
            'record' => $record
        ]);
    }

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
