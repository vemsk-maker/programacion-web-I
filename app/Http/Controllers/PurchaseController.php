<?php

namespace App\Http\Controllers;

use App\Enums\DocumentStatus;
use App\Enums\DocumentType;
use App\Enums\LocationType;
use App\Enums\MovementType;
use App\Http\Requests\StorePurchaseRequest;
use App\Models\Document;
use App\Models\Category;
use App\Models\Location;
use App\Models\MovementGroup;
use App\Models\Product;
use App\Models\Supplier;
use App\Services\InventoryService;
use Illuminate\Http\Request;

class PurchaseController extends Controller
{
    public function __construct(private InventoryService $inventory) {}

    public function index(Request $request)
    {
        $purchases = MovementGroup::where('type', MovementType::Purchase)
            ->with([
                'movements.toLocation',
                'movements.batch.supplier',
                'document',
            ])
            ->when($request->date_from, fn ($q, $v) => $q->whereDate('created_at', '>=', $v))
            ->when($request->date_to,   fn ($q, $v) => $q->whereDate('created_at', '<=', $v))
            ->when($request->supplier_id, function ($q, $supplierId) {
                $q->whereHas('movements.batch', fn ($q) => $q->where('supplier_id', $supplierId));
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $suppliers = Supplier::orderBy('name')->get(['id', 'name']);

        return view('purchases.index', compact('purchases', 'suppliers'));
    }

    public function create()
    {
        $suppliers = Supplier::where('active', true)->orderBy('name')->get(['id', 'name']);

        $locations = Location::whereIn('type', [
            LocationType::Warehouse->value,
            LocationType::Store->value,
        ])->orderBy('name')->get(['id', 'name', 'type']);

        $products = Product::with(['barcodes:product_id,barcode', 'category:id,name'])
            ->where('active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'use_batches', 'category_id', 'sale_price']);

        $categories = Category::orderBy('name')->get(['id', 'name']);

        return view('purchases.create', compact('suppliers', 'locations', 'products', 'categories'));
    }

    public function store(StorePurchaseRequest $request)
    {
        $validated = $request->validated();

        $data = [
            'supplier_id'   => $validated['supplier_id'],
            'location_id'   => $validated['location_id'],
            'user_id'       => auth()->id(),
            'reference_doc' => $validated['reference_doc'] ?? null,
            'notes'         => $validated['notes'] ?? null,
            'lines'         => $validated['lines'],
        ];

        try {
            $group = $this->inventory->registerPurchase($data);
        } catch (\Throwable $e) {
            return back()
                ->withInput()
                ->with('error', 'Error al registrar la compra: ' . $e->getMessage());
        }

        $total = collect($validated['lines'])
            ->sum(fn ($l) => (float) $l['quantity'] * (float) $l['unit_cost']);

        $docNumber = 'REC-' . str_pad($group->id, 6, '0', STR_PAD_LEFT);

        Document::create([
            'movement_group_id' => $group->id,
            'document_type'     => DocumentType::Receipt,
            'doc_number'        => $docNumber,
            'total_amount'      => $total,
            'status'            => DocumentStatus::Closed,
        ]);

        return redirect()
            ->route('inventory.purchases.show', $group->id)
            ->with('success', "Compra {$docNumber} registrada correctamente.");
    }

    public function show(int $id)
    {
        $group = MovementGroup::where('type', MovementType::Purchase)
            ->with([
                'movements.product',
                'movements.batch.supplier',
                'movements.toLocation',
                'document',
                'user',
            ])
            ->findOrFail($id);

        return view('purchases.show', compact('group'));
    }
}
