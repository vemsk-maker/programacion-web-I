<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSupplierRequest;
use App\Http\Requests\UpdateSupplierRequest;
use App\Models\Supplier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SupplierController extends Controller
{
    public function index(Request $request): View
    {
        $suppliers = Supplier::when($request->search, fn ($q, $s) => $q->where('name', 'ilike', "%{$s}%"))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('suppliers.index', compact('suppliers'));
    }

    public function create(): View
    {
        return view('suppliers.create');
    }

    public function store(StoreSupplierRequest $request): RedirectResponse
    {
        Supplier::create($request->validated());

        return redirect()->route('suppliers.index')
            ->with('success', 'Proveedor creado correctamente.');
    }

    public function edit(Supplier $supplier): View
    {
        return view('suppliers.edit', compact('supplier'));
    }

    public function update(UpdateSupplierRequest $request, Supplier $supplier): RedirectResponse
    {
        $supplier->update($request->validated());

        return redirect()->route('suppliers.index')
            ->with('success', 'Proveedor actualizado correctamente.');
    }

    public function destroy(Supplier $supplier): RedirectResponse
    {
        if ($supplier->batches()->exists()) {
            return back()->with('error', 'No se puede eliminar: el proveedor tiene lotes registrados.');
        }

        $supplier->delete();

        return redirect()->route('suppliers.index')
            ->with('success', 'Proveedor eliminado correctamente.');
    }

    public function toggle(Supplier $supplier): RedirectResponse
    {
        $supplier->update(['active' => ! $supplier->active]);

        $state = $supplier->active ? 'activado' : 'desactivado';

        return back()->with('success', "Proveedor {$state} correctamente.");
    }
}
