<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class UserAdminController extends Controller
{
    private function ensureAdmin(Request $request): void
    {
        abort_unless($request->user()?->hasRole('admin'), 403);
    }

    public function index(Request $request)
    {
        $this->ensureAdmin($request);

        $users = User::query()
            ->with(['roles', 'permissions'])
            ->orderBy('name')
            ->get()
            ->map(fn (User $user) => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'roles' => $user->roles->pluck('name')->values(),
                'permissions' => $user->getAllPermissions()->pluck('name')->values(),
                'created_at' => optional($user->created_at)->toIso8601String(),
            ]);

        return Inertia::render('admin/UsersIndex', [
            'users' => $users,
        ]);
    }

    public function create(Request $request)
    {
        $this->ensureAdmin($request);

        $roles = Role::query()
            ->orderBy('name')
            ->pluck('name')
            ->values();

        $permissions = Permission::query()
            ->orderBy('name')
            ->pluck('name')
            ->values();

        return Inertia::render('admin/UsersCreate', [
            'roles' => $roles,
            'permissions' => $permissions,
        ]);
    }

    public function store(Request $request)
    {
        $this->ensureAdmin($request);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:191'],
            'email' => ['required', 'email', 'max:191', Rule::unique('users', 'email')],
            'password' => ['required', 'string', 'min:8'],
            'roles' => ['required', 'array', 'min:1'],
            'roles.*' => ['string', Rule::exists('roles', 'name')],
            'permissions' => ['array'],
            'permissions.*' => ['string', Rule::exists('permissions', 'name')],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        $user->syncRoles($data['roles'] ?? []);
        $user->syncPermissions($data['permissions'] ?? []);

        return back()->with('success', 'Conta criada.');
    }
}
