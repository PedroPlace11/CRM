<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AccessAdminController extends Controller
{
    private function ensureAdmin(Request $request): void
    {
        abort_unless($request->user()?->hasRole('admin'), 403);
    }

    public function index(Request $request)
    {
        $this->ensureAdmin($request);

        $predefinedPermissions = [
            'deals.view',
            'deals.create',
            'people.view',
        ];

        foreach ($predefinedPermissions as $permissionName) {
            Permission::firstOrCreate(['name' => $permissionName]);
        }

        $roles = Role::query()
            ->with('permissions')
            ->orderBy('name')
            ->get()
            ->map(fn (Role $role) => [
                'id' => $role->id,
                'name' => $role->name,
                'permissions' => $role->permissions->pluck('name')->values(),
            ]);

        $permissions = Permission::query()
            ->orderBy('name')
            ->get()
            ->map(fn (Permission $permission) => [
                'id' => $permission->id,
                'name' => $permission->name,
            ]);

        $permissionOptions = Permission::query()
            ->orderBy('name')
            ->pluck('name')
            ->values();

        return Inertia::render('admin/AccessIndex', [
            'roles' => $roles,
            'permissions' => $permissions,
            'permissionOptions' => $permissionOptions,
        ]);
    }

    public function storeRole(Request $request)
    {
        $this->ensureAdmin($request);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:191', Rule::unique('roles', 'name')],
            'permissions' => ['array'],
            'permissions.*' => ['string', Rule::exists('permissions', 'name')],
        ]);

        $role = Role::create([
            'name' => $data['name'],
        ]);

        $role->syncPermissions($data['permissions'] ?? []);

        return back()->with('success', 'Cargo criado.');
    }

    public function destroyRole(Request $request, Role $role)
    {
        $this->ensureAdmin($request);

        if ($role->name === 'admin') {
            return back()->with('error', 'O cargo admin nao pode ser removido.');
        }

        $role->delete();

        return back()->with('success', 'Cargo removido.');
    }

    public function destroyPermission(Request $request, Permission $permission)
    {
        $this->ensureAdmin($request);

        $permission->delete();

        return back()->with('success', 'Permissao removida.');
    }
}
