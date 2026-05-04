<?php

namespace App\Http\Controllers;

use App\Enums\MovementType;
use App\Models\Location;
use App\Models\MovementGroup;
use App\Models\Product;
use App\Models\StockCache;
use App\Services\InventoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use RuntimeException;

class AdjustmentController extends Controller
{
    public function __construct(private InventoryService $inventory) {}

    public function index(Request $request): View
    {
        $locations = Location::orderBy('name')->get(['id', 'name']);

        $adjustments = MovementGroup::where('type', MovementType::Adjustment)
            ->with([
                'movements.product',
                'movements.toLocation',
                'movements.fromLocation',
                'user',
            ])
            ->when($request->date_from, fn ($q, $v) => $q->whereDate('created_at', '>=', $v))
            ->when($request->date_to,   fn ($q, $v) => $q->whereDate('created_at', '<=', $v))
            ->when($request->location_id, fn ($q, $v) =>
                $q->where('origin_location_id', $v)
            )
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('inventory.adjustments.index', compact('adjustments', 'locations'));
    }

    public function create(): View
    {
        $locations = Location::where('active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'type']);

        $products = Product::where('active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'use_batches']);

        return view('inventory.adjustments.create', compact('locations', 'products'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'location_id'        => ['required', 'integer', 'exists:locations,id'],
            'notes'              => ['nullable', 'string', 'max:500'],
            'lines'              => ['required', 'array', 'min:1'],
            'lines.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'lines.*.quantity'   => ['required', 'numeric', 'not_in:0'],
            'lines.*.unit_cost'  => ['nullable', 'numeric', 'min:0'],
        ]);

        try {
            $this->inventory->registerAdjustment([
                'user_id'     => Auth::id(),
                'location_id' => $data['location_id'],
                'notes'       => $data['notes'] ?? null,
                'lines'       => $data['lines'],
            ]);
        } catch (RuntimeException $e) {
            return back()->withInput()
                ->with('error', 'Error al registrar ajuste: ' . $e->getMessage());
        }

        return redirect()->route('inventory.adjustments.index')
            ->with('success', 'Ajuste de inventario registrado correctamente.');
    }

    public function show(int $id): View
    {
        $adjustment = MovementGroup::with([
            'movements.product',
            'movements.toLocation',
            'movements.fromLocation',
            'user',
        ])->findOrFail($id);

        return view('inventory.adjustments.show', compact('adjustment'));
    }
}
