<?php

namespace App\Http\Controllers;

use App\Enums\RoleName;
use App\Models\Location;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    /** Roles that must be assigned to at least one location */
    private const ROLES_NEED_LOCATION = [
        RoleName::WarehouseManager->value,
        RoleName::Cashier->value,
        RoleName::Viewer->value,
    ];

    public function index(Request $request): View
    {
        $users = User::with(['role', 'locations'])
            ->when($request->search, fn ($q, $s) =>
                $q->where('name', 'ilike', "%{$s}%")
                  ->orWhere('email', 'ilike', "%{$s}%")
            )
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    public function create(): View
    {
        $roles     = Role::orderBy('id')->get();
        $locations = Location::where('active', true)
            ->whereIn('type', ['store', 'warehouse'])
            ->orderBy('name')
            ->get(['id', 'name', 'type']);

        return view('admin.users.create', compact('roles', 'locations'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'         => ['required', 'string', 'max:255'],
            'email'        => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'password'     => ['required', 'string', 'min:8', 'confirmed'],
            'role_id'      => ['required', 'integer', 'exists:roles,id'],
            'location_ids' => ['nullable', 'array'],
            'location_ids.*' => ['integer', 'exists:locations,id'],
            'active'       => ['nullable', 'boolean'],
        ]);

        $roleName = Role::findOrFail($data['role_id'])->name->value;

        if (in_array($roleName, self::ROLES_NEED_LOCATION, true)) {
            if (empty($data['location_ids'])) {
                return back()->withInput()
                    ->withErrors(['location_ids' => 'Este rol requiere asignar al menos una ubicación.']);
            }
        }

        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
            'role_id'  => $data['role_id'],
            'active'   => $request->boolean('active', true),
        ]);

        if (! empty($data['location_ids'])) {
            $user->locations()->sync($data['location_ids']);
        }

        return redirect()->route('admin.users.index')
            ->with('success', 'Usuario creado correctamente.');
    }

    public function edit(int $id): View
    {
        $user      = User::with(['role', 'locations'])->findOrFail($id);
        $roles     = Role::orderBy('id')->get();
        $locations = Location::where('active', true)
            ->whereIn('type', ['store', 'warehouse'])
            ->orderBy('name')
            ->get(['id', 'name', 'type']);

        $assignedLocationIds = $user->locations->pluck('id')->toArray();

        return view('admin.users.edit', compact('user', 'roles', 'locations', 'assignedLocationIds'));
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $user = User::findOrFail($id);

        $data = $request->validate([
            'name'           => ['required', 'string', 'max:255'],
            'email'          => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password'       => ['nullable', 'string', 'min:8', 'confirmed'],
            'role_id'        => ['required', 'integer', 'exists:roles,id'],
            'location_ids'   => ['nullable', 'array'],
            'location_ids.*' => ['integer', 'exists:locations,id'],
            'active'         => ['nullable', 'boolean'],
        ]);

        $roleName = Role::findOrFail($data['role_id'])->name->value;

        if (in_array($roleName, self::ROLES_NEED_LOCATION, true)) {
            if (empty($data['location_ids'])) {
                return back()->withInput()
                    ->withErrors(['location_ids' => 'Este rol requiere asignar al menos una ubicación.']);
            }
        }

        $updateData = [
            'name'    => $data['name'],
            'email'   => $data['email'],
            'role_id' => $data['role_id'],
            'active'  => $request->boolean('active', true),
        ];

        if (! empty($data['password'])) {
            $updateData['password'] = Hash::make($data['password']);
        }

        $user->update($updateData);
        $user->locations()->sync($data['location_ids'] ?? []);

        return redirect()->route('admin.users.index')
            ->with('success', 'Usuario actualizado correctamente.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $user = User::with('role')->findOrFail($id);

        if ($user->id === Auth::id()) {
            return back()->with('error', 'No puedes eliminar tu propia cuenta.');
        }

        if ($user->role->name === RoleName::Master) {
            $masterCount = User::whereHas('role', fn ($q) => $q->where('name', RoleName::Master->value))->count();
            if ($masterCount <= 1) {
                return back()->with('error', 'No se puede eliminar el único usuario master del sistema.');
            }
        }

        if ($user->movementGroups()->exists()) {
            return back()->with('error', 'No se puede eliminar: el usuario tiene movimientos registrados.');
        }

        $user->locations()->detach();
        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'Usuario eliminado correctamente.');
    }

    public function toggle(int $id): RedirectResponse
    {
        $user = User::findOrFail($id);

        if ($user->id === Auth::id()) {
            return back()->with('error', 'No puedes desactivar tu propia cuenta.');
        }

        $user->update(['active' => ! $user->active]);
        $state = $user->active ? 'activado' : 'desactivado';

        return back()->with('success', "Usuario {$state} correctamente.");
    }
}
