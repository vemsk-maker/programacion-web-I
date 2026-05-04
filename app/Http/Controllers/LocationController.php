<?php

namespace App\Http\Controllers;

use App\Enums\LocationType;
use App\Models\Location;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class LocationController extends Controller
{
    public function index(Request $request): View
    {
        $locations = Location::with('children')
            ->whereNull('parent_id')
            ->when($request->search, fn ($q, $s) =>
                $q->where('name', 'ilike', "%{$s}%")
            )
            ->orderBy('type')
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('admin.locations.index', compact('locations'));
    }

    public function create(): View
    {
        $stores = Location::where('type', LocationType::Store)
            ->where('active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('admin.locations.create', compact('stores'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'              => ['required', 'string', 'max:255'],
            'type'              => ['required', Rule::enum(LocationType::class)],
            'parent_id'         => ['nullable', 'integer', 'exists:locations,id'],
            'active'            => ['nullable', 'boolean'],
            'create_warehouse'  => ['nullable', 'boolean'],
            'warehouse_name'    => ['nullable', 'string', 'max:255'],
        ]);

        $location = Location::create([
            'name'      => $data['name'],
            'type'      => $data['type'],
            'parent_id' => $data['parent_id'] ?? null,
            'active'    => $request->boolean('active', true),
        ]);

        // If it's a store and user wants to auto-create its warehouse
        if ($data['type'] === LocationType::Store->value && $request->boolean('create_warehouse')) {
            $warehouseName = ! empty($data['warehouse_name'])
                ? $data['warehouse_name']
                : 'Almacén ' . $data['name'];

            Location::create([
                'name'      => $warehouseName,
                'type'      => LocationType::Warehouse,
                'parent_id' => $location->id,
                'active'    => true,
            ]);
        }

        return redirect()->route('admin.locations.index')
            ->with('success', 'Ubicación creada correctamente.');
    }

    public function edit(int $id): View
    {
        $location = Location::findOrFail($id);

        $stores = Location::where('type', LocationType::Store)
            ->where('id', '!=', $id)
            ->where('active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('admin.locations.edit', compact('location', 'stores'));
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $location = Location::findOrFail($id);

        $data = $request->validate([
            'name'      => ['required', 'string', 'max:255'],
            'type'      => ['required', Rule::enum(LocationType::class)],
            'parent_id' => ['nullable', 'integer', 'exists:locations,id', Rule::notIn([$id])],
            'active'    => ['nullable', 'boolean'],
        ]);

        $location->update([
            'name'      => $data['name'],
            'type'      => $data['type'],
            'parent_id' => $data['parent_id'] ?? null,
            'active'    => $request->boolean('active', true),
        ]);

        return redirect()->route('admin.locations.index')
            ->with('success', 'Ubicación actualizada correctamente.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $location = Location::with('children')->findOrFail($id);

        if ($location->stockCache()->exists()) {
            return back()->with('error', 'No se puede eliminar: la ubicación tiene stock registrado.');
        }

        if ($location->children()->exists()) {
            return back()->with('error', 'No se puede eliminar: la ubicación tiene sub-ubicaciones asociadas.');
        }

        $location->users()->detach();
        $location->delete();

        return redirect()->route('admin.locations.index')
            ->with('success', 'Ubicación eliminada correctamente.');
    }

    public function toggle(int $id): RedirectResponse
    {
        $location = Location::findOrFail($id);
        $location->update(['active' => ! $location->active]);
        $state = $location->active ? 'activada' : 'desactivada';

        return back()->with('success', "Ubicación {$state} correctamente.");
    }
}
