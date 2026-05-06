<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductBarcode;
use App\Models\StockCache;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $products = Product::with('category')
            ->when($request->search, fn ($q, $s) => $q->where('products.name', 'ilike', "%{$s}%"))
            ->when($request->category_id, fn ($q, $id) => $q->where('category_id', $id))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        $categories = Category::orderBy('name')->get();

        return view('products.index', compact('products', 'categories'));
    }

    public function create(): View
    {
        $categories = Category::orderBy('name')->get();

        return view('products.create', compact('categories'));
    }

    public function store(StoreProductRequest $request): RedirectResponse
    {
        return DB::transaction(function () use ($request) {
            $product = Product::create($request->safe()->except('barcodes'));

            $this->syncBarcodes($product, $request->input('barcodes', []));

            return redirect()->route('products.show', $product)
                ->with('success', 'Producto creado correctamente.');
        });
    }

    public function show(Product $product): View
    {
        $product->load(['category', 'barcodes']);

        $stock = StockCache::with(['location', 'batch'])
            ->where('product_id', $product->id)
            ->get();

        return view('products.show', compact('product', 'stock'));
    }

    public function edit(Product $product): View
    {
        $product->load('barcodes');
        $categories = Category::orderBy('name')->get();

        return view('products.edit', compact('product', 'categories'));
    }

    public function update(UpdateProductRequest $request, Product $product): RedirectResponse
    {
        return DB::transaction(function () use ($request, $product) {
            $product->update($request->safe()->except('barcodes'));

            $this->syncBarcodes($product, $request->input('barcodes', []));

            return redirect()->route('products.show', $product)
                ->with('success', 'Producto actualizado correctamente.');
        });
    }

    public function destroy(Product $product): RedirectResponse
    {
        if ($product->batches()->exists()) {
            return back()->with('error', 'No se puede eliminar: el producto tiene lotes registrados.');
        }

        if (StockCache::where('product_id', $product->id)->where('quantity', '>', 0)->exists()) {
            return back()->with('error', 'No se puede eliminar: el producto tiene stock activo.');
        }

        $product->delete();

        return redirect()->route('products.index')
            ->with('success', 'Producto eliminado correctamente.');
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function syncBarcodes(Product $product, array $barcodes): void
    {
        // Remove all existing and re-insert submitted ones
        $product->barcodes()->delete();

        foreach ($barcodes as $barcode) {
            if (empty($barcode['barcode'])) {
                continue;
            }

            ProductBarcode::create([
                'product_id'    => $product->id,
                'barcode'       => $barcode['barcode'],
                'units_per_scan' => (int) ($barcode['units_per_scan'] ?? 1),
            ]);
        }
    }
}
