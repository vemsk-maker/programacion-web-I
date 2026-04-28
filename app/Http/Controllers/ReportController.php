<?php

namespace App\Http\Controllers;

use App\Enums\MovementType;
use App\Enums\RoleName;
use App\Models\Batch;
use App\Models\Category;
use App\Models\InventoryMovement;
use App\Models\Location;
use App\Models\MovementGroup;
use App\Models\Product;
use App\Models\StockCache;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    // ── Helper: does the current user see cost columns? ───────────────────────
    private function canSeeCosts(): bool
    {
        $role = auth()->user()?->role?->name;
        return in_array($role, [RoleName::Master, RoleName::Admin, RoleName::WarehouseManager], true);
    }

    // =========================================================================
    // 1. DASHBOARD  GET /reports
    // =========================================================================
    public function dashboard()
    {
        $today       = now()->toDateString();
        $in30        = now()->addDays(30)->toDateString();

        // Lotes que vencen en los próximos 30 días y tienen stock > 0
        $expiringCount = DB::table('batches as b')
            ->join('stock_cache as sc', 'sc.batch_id', '=', 'b.id')
            ->where('sc.quantity', '>', 0)
            ->whereDate('b.expiration_date', '<=', $in30)
            ->whereDate('b.expiration_date', '>=', $today)
            ->whereNotNull('b.expiration_date')
            ->count();

        // Lotes ya vencidos con stock > 0 (crítico)
        $expiredCount = DB::table('batches as b')
            ->join('stock_cache as sc', 'sc.batch_id', '=', 'b.id')
            ->where('sc.quantity', '>', 0)
            ->whereDate('b.expiration_date', '<', $today)
            ->whereNotNull('b.expiration_date')
            ->count();

        // Total productos activos
        $activeProducts = Product::where('active', true)->count();

        // Movimientos de hoy
        $todayMovements = InventoryMovement::whereDate('created_at', $today)->count();

        return view('reports.dashboard', compact(
            'expiringCount',
            'expiredCount',
            'activeProducts',
            'todayMovements'
        ));
    }

    // =========================================================================
    // 2. EXPIRATION ALERTS  GET /reports/expirations
    // =========================================================================
    public function expirations(Request $request)
    {
        $days       = max(1, (int) $request->input('days', 30));
        $locationId = $request->input('location_id');
        $export     = $request->boolean('export');

        $query = DB::table('batches as b')
            ->join('stock_cache as sc', 'sc.batch_id', '=', 'b.id')
            ->join('products as p', 'p.id', '=', 'b.product_id')
            ->join('locations as l', 'l.id', '=', 'sc.location_id')
            ->where('sc.quantity', '>', 0)
            ->whereNotNull('b.expiration_date')
            ->whereRaw("b.expiration_date <= CURRENT_DATE + INTERVAL '{$days} days'")
            ->when($locationId, fn ($q) => $q->where('sc.location_id', $locationId))
            ->select(
                'b.id as batch_id',
                'b.batch_code',
                'b.expiration_date',
                'p.name as product',
                'l.name as location',
                'sc.quantity'
            )
            ->orderBy('b.expiration_date');

        if ($export) {
            $rows = $query->get();
            return $this->exportCsv(
                'vencimientos_' . now()->format('Ymd') . '.csv',
                ['Lote', 'Producto', 'Ubicación', 'Vencimiento', 'Stock'],
                $rows->map(fn ($r) => [
                    $r->batch_code,
                    $r->product,
                    $r->location,
                    $r->expiration_date,
                    $r->quantity,
                ])->toArray()
            );
        }

        $batches   = $query->get();
        $locations = Location::where('active', true)->orderBy('name')->get(['id', 'name']);

        return view('reports.expirations', compact('batches', 'locations', 'days', 'locationId'));
    }

    // =========================================================================
    // 3. STOCK VALUATION  GET /reports/stock
    // =========================================================================
    public function stock(Request $request)
    {
        $locationId = $request->input('location_id');
        $categoryId = $request->input('category_id');
        $export     = $request->boolean('export');
        $canCost    = $this->canSeeCosts();

        // Last unit_cost per product from purchase movements
        $lastCostSub = DB::table('inventory_movements as im')
            ->join('movement_groups as mg', 'mg.id', '=', 'im.group_id')
            ->where('mg.type', MovementType::Purchase->value)
            ->whereNotNull('im.unit_cost')
            ->select('im.product_id', DB::raw('MAX(im.id) as last_movement_id'))
            ->groupBy('im.product_id');

        $query = DB::table('stock_cache as sc')
            ->join('products as p', 'p.id', '=', 'sc.product_id')
            ->join('locations as l', 'l.id', '=', 'sc.location_id')
            ->leftJoin('categories as c', 'c.id', '=', 'p.category_id')
            ->leftJoinSub($lastCostSub, 'lc', 'lc.product_id', '=', 'sc.product_id')
            ->leftJoin('inventory_movements as im2', 'im2.id', '=', 'lc.last_movement_id')
            ->where('sc.quantity', '>', 0)
            ->when($locationId, fn ($q) => $q->where('sc.location_id', $locationId))
            ->when($categoryId, fn ($q) => $q->where('p.category_id', $categoryId))
            ->select(
                'p.id as product_id',
                'p.name as product',
                'p.unit_of_measure',
                'c.name as category',
                'l.id as location_id',
                'l.name as location',
                DB::raw('SUM(sc.quantity) as total_qty'),
                DB::raw('MAX(im2.unit_cost) as unit_cost')
            )
            ->groupBy('p.id', 'p.name', 'p.unit_of_measure', 'c.name', 'l.id', 'l.name')
            ->orderBy('l.name')
            ->orderBy('p.name');

        if ($export) {
            $rows = $query->get();
            $headers = ['Producto', 'Categoría', 'Ubicación', 'Cantidad', 'Unidad'];
            if ($canCost) {
                $headers[] = 'Costo Unit.';
                $headers[] = 'Valor Total';
            }
            return $this->exportCsv(
                'stock_' . now()->format('Ymd') . '.csv',
                $headers,
                $rows->map(function ($r) use ($canCost) {
                    $row = [$r->product, $r->category ?? '—', $r->location, $r->total_qty, $r->unit_of_measure ?? ''];
                    if ($canCost) {
                        $row[] = number_format($r->unit_cost ?? 0, 2);
                        $row[] = number_format(($r->total_qty ?? 0) * ($r->unit_cost ?? 0), 2);
                    }
                    return $row;
                })->toArray()
            );
        }

        $rows       = $query->paginate(50)->withQueryString();
        $locations  = Location::where('active', true)->orderBy('name')->get(['id', 'name']);
        $categories = Category::whereNull('parent_id')->orderBy('name')->get(['id', 'name']);

        $totalValue = $canCost
            ? DB::table('stock_cache as sc')
                ->join('products as p', 'p.id', '=', 'sc.product_id')
                ->leftJoinSub($lastCostSub, 'lc', 'lc.product_id', '=', 'sc.product_id')
                ->leftJoin('inventory_movements as im2', 'im2.id', '=', 'lc.last_movement_id')
                ->where('sc.quantity', '>', 0)
                ->when($locationId, fn ($q) => $q->where('sc.location_id', $locationId))
                ->when($categoryId, fn ($q) => $q->where('p.category_id', $categoryId))
                ->sum(DB::raw('sc.quantity * COALESCE(im2.unit_cost, 0)'))
            : null;

        return view('reports.stock', compact(
            'rows', 'locations', 'categories',
            'locationId', 'categoryId', 'canCost', 'totalValue'
        ));
    }

    // =========================================================================
    // 4. MOVEMENTS KARDEX  GET /reports/movements
    // =========================================================================
    public function movements(Request $request)
    {
        $productId  = $request->input('product_id');
        $locationId = $request->input('location_id');
        $type       = $request->input('type');
        $dateFrom   = $request->input('date_from');
        $dateTo     = $request->input('date_to');
        $export     = $request->boolean('export');
        $canCost    = $this->canSeeCosts();

        $query = InventoryMovement::with([
            'product:id,name',
            'batch:id,batch_code',
            'fromLocation:id,name',
            'toLocation:id,name',
            'group:id,type,reference_doc,user_id',
            'group.user:id,name',
        ])
            ->when($productId,  fn ($q) => $q->where('product_id', $productId))
            ->when($locationId, fn ($q) => $q->where(function ($q2) use ($locationId) {
                $q2->where('from_location_id', $locationId)
                   ->orWhere('to_location_id', $locationId);
            }))
            ->when($type, fn ($q) => $q->whereHas('group', fn ($q2) => $q2->where('type', $type)))
            ->when($dateFrom, fn ($q) => $q->whereDate('created_at', '>=', $dateFrom))
            ->when($dateTo,   fn ($q) => $q->whereDate('created_at', '<=', $dateTo))
            ->latest()
            ->select('inventory_movements.*');

        if ($export) {
            $rows    = $query->get();
            $headers = ['Fecha', 'Tipo', 'Producto', 'Lote', 'Origen', 'Destino', 'Cantidad', 'Usuario', 'Referencia'];
            if ($canCost) {
                $headers[] = 'Costo Unit.';
            }
            return $this->exportCsv(
                'movimientos_' . now()->format('Ymd') . '.csv',
                $headers,
                $rows->map(function ($m) use ($canCost) {
                    $row = [
                        $m->created_at->format('d/m/Y H:i'),
                        $m->group?->type?->value ?? '',
                        $m->product?->name ?? '',
                        $m->batch?->batch_code ?? '',
                        $m->fromLocation?->name ?? '',
                        $m->toLocation?->name ?? '',
                        $m->quantity,
                        $m->group?->user?->name ?? '',
                        $m->group?->reference_doc ?? '',
                    ];
                    if ($canCost) {
                        $row[] = number_format($m->unit_cost ?? 0, 2);
                    }
                    return $row;
                })->toArray()
            );
        }

        $movements  = $query->paginate(50)->withQueryString();
        $locations  = Location::where('active', true)->orderBy('name')->get(['id', 'name']);
        $products   = Product::where('active', true)->orderBy('name')->get(['id', 'name']);
        $types      = MovementType::cases();

        return view('reports.movements', compact(
            'movements', 'locations', 'products', 'types',
            'productId', 'locationId', 'type', 'dateFrom', 'dateTo', 'canCost'
        ));
    }

    // =========================================================================
    // Private: CSV streaming helper
    // =========================================================================
    private function exportCsv(string $filename, array $headers, array $rows): Response
    {
        return response()->streamDownload(function () use ($headers, $rows) {
            $handle = fopen('php://output', 'w');
            // UTF-8 BOM for Excel compatibility
            fputs($handle, "\xEF\xBB\xBF");
            fputcsv($handle, $headers);
            foreach ($rows as $row) {
                fputcsv($handle, $row);
            }
            fclose($handle);
        }, $filename, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }
}
