<?php

namespace App\Http\Controllers\Api\v1;

use App\Models\Record;
use App\Policies\UserPolicy;
use Auth;
use Gate;
use Orion\Concerns\DisablePagination;
use Orion\Concerns\HandlesAuthorization;
use Orion\Http\Controllers\Controller;
use App\Models\User;
use Orion\Http\Requests\Request as OrionRequest;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    use HandlesAuthorization;

    protected $model = User::class;

    public function store(OrionRequest $request)
    {

        Gate::authorize('create', User::class);

        $auth = $request->user();

        $data = $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:6',
            'role' => 'required|array',
            'role.*' => 'string|exists:roles,name',
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
            'action' => "create",
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
        Gate::authorize('update', User::class);

        $id = $args[0];

        $data = $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email,' . $id,
            'role' => 'required|array',
            'role.*' => 'string|exists:roles,name',
        ]);

        $user = User::findOrFail($id);

        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->password;
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

        Gate::authorize('view', User::class);

        $search = $request->get('name') ?? $request->get('email');
        $page = $request->get('per_page');
        $all = $request->boolean('all', false);

        $query = User::orderBy('id', 'Asc')
            ->where('id', '!=', auth()->id())
            ->nameOrEmail($search)
            ->with('center', 'roles', 'permissions');

        $all ? $users = $query->get() : $users = $query->paginate($page ? $page : 10);

        return response()->json($users);
    }

    public function show(OrionRequest $request, ...$args)
    {

        Gate::authorize('viewAny', User::class);

        $id = $args[0];

        $users = User::with('center', 'roles', 'roles.permissions')
            ->where('id', '!=', auth()->id())
            ->findOrFail($id);
        return response()->json($users);
    }

    public function destroy(OrionRequest $request, ...$args)
    {

        Gate::authorize('delete', User::class);

        $id = $args[0];

        $user = User::findOrFail($id);
        $user->delete();

        $record = Record::create([
            'user_id' => $request->user()->id,
            'name' => $request->user()->name,
            'action' => "delete",
            'affected_table' => "users",
            'affected_record_id' => $user->id,
        ]);

        return response()->json(['message' => 'Users deleted successfully', 'record' => $record]);
    }

    public function count(Request $request)
    {
        $query = User::orderBy('id', 'Asc');
        $users = $query->get();

        $userCount = $users->count();
        $superAdminCount = 0;
        $visorCount = 0;

        foreach ($users as $user) {
            foreach ($user->roles as $role) {
                switch ($role->name) {
                    case 'superAdmin':
                        $superAdminCount++;
                        break;
                    case 'visor':
                        $visorCount++;
                        break;
                    default:
                        break;
                }
            }
        }

        return response()->json([
            'users_account' => $userCount,
            'super_admins_account' => $superAdminCount,
            'visors_account' => $visorCount,
        ]);
    }
}
