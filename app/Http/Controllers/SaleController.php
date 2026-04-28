<?php

namespace App\Http\Controllers;

use App\Enums\DocumentStatus;
use App\Enums\DocumentType;
use App\Enums\LocationType;
use App\Enums\MovementType;
use App\Http\Requests\StoreSaleRequest;
use App\Models\Document;
use App\Models\Location;
use App\Models\MovementGroup;
use App\Models\Product;
use App\Services\InventoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SaleController extends Controller
{
    public function __construct(private InventoryService $inventory) {}

    // ── History ───────────────────────────────────────────────────────────────
    public function index(Request $request)
    {
        $user = auth()->user();

        $query = MovementGroup::where('type', MovementType::Sale)
            ->with(['document', 'user', 'movements' => fn ($q) => $q->whereNotNull('from_location_id')->limit(1), 'movements.fromLocation'])
            ->when($request->date_from, fn ($q, $v) => $q->whereDate('created_at', '>=', $v))
            ->when($request->date_to,   fn ($q, $v) => $q->whereDate('created_at', '<=', $v));

        // Cashiers only see their own locations
        if ($user->isCashier()) {
            $locationIds = $user->locations->pluck('id');
            $query->whereIn('origin_location_id', $locationIds);
        } elseif ($request->location_id) {
            $query->where('origin_location_id', $request->location_id);
        }

        $sales = $query->latest()->paginate(25)->withQueryString();

        $locations = $user->isAdmin()
            ? Location::where('active', true)->orderBy('name')->get(['id', 'name'])
            : collect();

        return view('sales.index', compact('sales', 'locations'));
    }

    // ── POS screen ────────────────────────────────────────────────────────────
    public function create()
    {
        $user = auth()->user();

        if ($user->isAdmin()) {
            $locations = Location::where('active', true)
                ->whereIn('type', [LocationType::Store->value, LocationType::Warehouse->value])
                ->orderBy('name')
                ->get(['id', 'name']);
        } else {
            $locations = $user->locations()
                ->where('active', true)
                ->orderBy('name')
                ->get(['id', 'name']);
        }

        return view('sales.create', compact('locations'));
    }

    // ── Register sale (JSON) ──────────────────────────────────────────────────
    public function store(StoreSaleRequest $request): JsonResponse
    {
        $validated = $request->validated();

        try {
            $result = DB::transaction(function () use ($validated) {
                $location = Location::findOrFail($validated['location_id']);

                // Derive a 3-letter code from location name for the doc number
                $stripped = preg_replace('/[^a-zA-Z]/', '', Str::ascii($location->name));
                $code     = strtoupper(substr($stripped, 0, 3)) ?: 'PSF';
                $year     = now()->format('Y');

                // Advisory lock per location prevents duplicate sequence numbers
                // under concurrent requests without holding a full table lock
                DB::statement('SELECT pg_advisory_xact_lock(?)', [$validated['location_id']]);

                $lastSeq = Document::where('document_type', DocumentType::Order)
                    ->where('doc_number', 'like', "{$code}-{$year}-%")
                    ->whereHas('movementGroup', fn ($q) => $q->where('origin_location_id', $validated['location_id']))
                    ->max(DB::raw("CAST(SPLIT_PART(doc_number, '-', 3) AS INTEGER)"));

                $seq       = ($lastSeq ?? 0) + 1;
                $docNumber = "{$code}-{$year}-" . str_pad($seq, 5, '0', STR_PAD_LEFT);

                $data = [
                    'user_id'       => auth()->id(),
                    'location_id'   => (int) $validated['location_id'],
                    'reference_doc' => $docNumber,
                    'notes'         => null,
                    'lines'         => array_map(fn ($l) => [
                        'product_id' => (int) $l['product_id'],
                        'quantity'   => (float) $l['quantity'],
                        'unit_cost'  => (float) $l['unit_price'],
                    ], $validated['lines']),
                ];

                $group = $this->inventory->registerSale($data);

                $total = collect($validated['lines'])
                    ->sum(fn ($l) => (float) $l['quantity'] * (float) $l['unit_price']);

                Document::create([
                    'movement_group_id' => $group->id,
                    'document_type'     => DocumentType::Order,
                    'doc_number'        => $docNumber,
                    'client_name'       => $validated['client_name'] ?? null,
                    'client_nit'        => $validated['client_nit'] ?? null,
                    'total_amount'      => $total,
                    'status'            => DocumentStatus::Open,
                ]);

                return ['group' => $group, 'docNumber' => $docNumber];
            });

            return response()->json([
                'success'    => true,
                'doc_number' => $result['docNumber'],
                'group_id'   => $result['group']->id,
                'show_url'   => route('sales.show', $result['group']->id),
            ]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    // ── Receipt detail ────────────────────────────────────────────────────────
    public function show(int $id)
    {
        $group = MovementGroup::where('type', MovementType::Sale)
            ->with([
                'movements.product',
                'movements.batch',
                'movements.fromLocation',
                'document',
                'user',
            ])
            ->findOrFail($id);

        return view('sales.show', compact('group'));
    }

    // ── Cancel (admin / master only) ──────────────────────────────────────────
    public function cancel(int $id)
    {
        if (! auth()->user()->isAdmin()) {
            abort(403, 'Solo administradores pueden cancelar ventas.');
        }

        $group = MovementGroup::with('document')->findOrFail($id);

        if ($group->document?->status !== DocumentStatus::Open) {
            return redirect()
                ->route('sales.show', $id)
                ->with('error', 'Esta venta ya fue cancelada o no puede ser revertida.');
        }

        try {
            $this->inventory->cancelMovementGroup($id, auth()->id());
        } catch (\Throwable $e) {
            return redirect()
                ->route('sales.show', $id)
                ->with('error', 'Error al cancelar la venta: ' . $e->getMessage());
        }

        return redirect()
            ->route('sales.show', $id)
            ->with('success', 'Venta cancelada y stock revertido correctamente.');
    }

    // ── Product search (AJAX) ─────────────────────────────────────────────────
    public function searchProduct(Request $request): JsonResponse
    {
        $q          = trim((string) $request->input('q', ''));
        $locationId = (int) $request->input('location_id', 0);

        if (strlen($q) < 2 || $locationId < 1) {
            return response()->json([]);
        }

        $products = Product::with([
            'barcodes:product_id,barcode',
            'stockCache' => fn ($query) => $query->where('location_id', $locationId),
        ])
            ->where('active', true)
            ->where(function ($query) use ($q) {
                $query->where('name', 'ilike', "%{$q}%")
                    ->orWhereHas('barcodes', fn ($q2) => $q2->where('barcode', $q));
            })
            ->limit(10)
            ->get(['id', 'name', 'unit_of_measure', 'use_batches']);

        return response()->json(
            $products->map(fn ($p) => [
                'id'              => $p->id,
                'name'            => $p->name,
                'unit_of_measure' => $p->unit_of_measure,
                'use_batches'     => (bool) $p->use_batches,
                'stock'           => (float) $p->stockCache->sum('quantity'),
            ])
        );
    }
}
