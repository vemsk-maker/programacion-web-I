@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Stock Actual" />

    @php
        $productsForTs = $products->map(fn ($p) => [
            'id'       => $p->id,
            'name'     => $p->name,
            'barcodes' => $p->barcodes->pluck('barcode')->toArray(),
        ])->values()->toArray();
    @endphp

    {{-- ── Filtros ── --}}
    <div class="rounded-[2.5rem] border border-gray-100 bg-white p-8 shadow-sm">
        <div class="mb-6 border-b border-gray-50 pb-6">
            <h3 class="text-2xl font-bold text-[#1e293b]">Stock Actual — Valorización</h3>
            <p class="text-sm text-gray-500">Inventario disponible por producto y ubicación</p>
        </div>

        <form method="GET" action="{{ route('reports.stock') }}"
              id="stock-filter-form"
              class="flex flex-wrap items-end gap-3">

            {{-- Producto (Tom Select + barcode) --}}
            <div class="w-full" x-data="stockProductFilter()" x-init="init()">
                <div class="flex flex-wrap items-end gap-3">
                    <div class="min-w-[220px] flex-1">
                        <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-widest text-gray-400">Producto</label>
                        <select id="ts-stock-product" class="w-full"></select>
                        <input type="hidden" name="product_id" :value="selectedId" />
                    </div>
                    <div class="w-52">
                        <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-widest text-gray-400">
                            Código de barras
                            <span class="ml-1 font-medium normal-case text-gray-300">(escaneo)</span>
                        </label>
                        <input type="text"
                            x-model="barcodeQuery"
                            @keydown.enter.prevent="onBarcodeEnter()"
                            @input="onBarcodeInput()"
                            placeholder="Escanee o escriba..."
                            autocomplete="off"
                            class="h-11 w-full rounded-xl border border-gray-200 bg-white px-4 font-mono text-sm text-gray-700 placeholder:text-gray-400 focus:border-gray-400 focus:outline-none transition-all" />
                        <p x-show="barcodeNotFound" class="mt-1 text-xs font-bold text-[#e11d48]">Código no encontrado.</p>
                    </div>
                    <button type="button" x-show="selectedId" @click="clear()"
                        class="flex h-11 items-center gap-1.5 rounded-xl px-3 text-xs font-bold text-gray-400 hover:bg-red-50 hover:text-[#e11d48] transition-all">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                        Quitar producto
                    </button>
                </div>
                <p x-show="selectedName" class="mt-1.5 text-xs font-bold text-emerald-600">
                    ✓ Mostrando: <span x-text="selectedName"></span>
                </p>
            </div>

            {{-- Categoría --}}
            <div>
                <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-widest text-gray-400">Categoría</label>
                <div class="relative">
                    <select name="category_id"
                        class="h-11 w-48 appearance-none rounded-xl border border-gray-200 bg-white px-4 pr-10 text-sm text-gray-700 focus:border-gray-400 focus:outline-none transition-all">
                        <option value="">Todas las categorías</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ $categoryId == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                    <span class="pointer-events-none absolute top-1/2 right-4 -translate-y-1/2 text-gray-400">
                        <svg width="16" height="16" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M4.79175 7.396L10.0001 12.6043L15.2084 7.396" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </span>
                </div>
            </div>

            {{-- Ubicación --}}
            <div>
                <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-widest text-gray-400">Ubicación</label>
                <div class="relative">
                    <select name="location_id"
                        class="h-11 w-48 appearance-none rounded-xl border border-gray-200 bg-white px-4 pr-10 text-sm text-gray-700 focus:border-gray-400 focus:outline-none transition-all">
                        <option value="">Todas las ubicaciones</option>
                        @foreach($locations as $loc)
                            <option value="{{ $loc->id }}" {{ $locationId == $loc->id ? 'selected' : '' }}>{{ $loc->name }}</option>
                        @endforeach
                    </select>
                    <span class="pointer-events-none absolute top-1/2 right-4 -translate-y-1/2 text-gray-400">
                        <svg width="16" height="16" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M4.79175 7.396L10.0001 12.6043L15.2084 7.396" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </span>
                </div>
            </div>

            <button type="submit" class="h-11 rounded-xl bg-[#1e293b] px-6 text-sm font-bold text-white hover:bg-[#334155] transition-all">
                Filtrar
            </button>
            <a href="{{ route('reports.stock') }}" class="text-sm font-bold text-gray-400 hover:text-[#e11d48] transition-colors">
                Limpiar
            </a>

            <a href="{{ route('reports.stock', array_merge(request()->query(), ['export' => 1])) }}"
                class="ml-auto flex h-11 items-center gap-2 rounded-xl border border-gray-200 bg-white px-5 text-sm font-bold text-gray-500 hover:bg-gray-50 transition-all">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                Exportar CSV
            </a>
        </form>
    </div>

    {{-- Valorización total (solo roles con costos) --}}
    @if($canCost && $totalValue !== null)
        <div class="my-5 flex items-center justify-between rounded-[2rem] border border-blue-100 bg-blue-50 px-8 py-5 shadow-sm">
            <div>
                <p class="text-[10px] font-bold uppercase tracking-widest text-blue-500">Valorización total del inventario</p>
                @if($locationId || $categoryId)
                    <p class="mt-0.5 text-xs font-medium text-blue-400">(Filtrado por los criterios seleccionados)</p>
                @endif
            </div>
            <span class="font-mono text-2xl font-black text-blue-700">
                Bs. {{ number_format((float) $totalValue, 2) }}
            </span>
        </div>
    @else
        <div class="my-5"></div>
    @endif

    {{-- Tabla --}}
    <div class="rounded-[2.5rem] border border-gray-100 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="text-[10px] font-bold uppercase tracking-widest text-gray-400">
                        <th class="pb-4 pl-8 pt-6">Producto</th>
                        <th class="pb-4 pt-6">Categoría</th>
                        <th class="pb-4 pt-6">Ubicación</th>
                        <th class="pb-4 pt-6 text-right">Cantidad</th>
                        <th class="pb-4 pt-6 text-center">UM</th>
                        @if($canCost)
                            <th class="pb-4 pt-6 text-right">Costo Unit.</th>
                            <th class="pb-4 pr-8 pt-6 text-right">Valor Total</th>
                        @else
                            <th class="pb-4 pr-8 pt-6"></th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 border-t border-gray-50">
                    @forelse($rows as $row)
                        <tr class="group transition-colors hover:bg-gray-50/50">
                            <td class="py-5 pl-8 text-sm font-bold text-[#1e293b]">
                                {{ $row->product }}
                            </td>
                            <td class="py-5 text-sm text-gray-500">
                                {{ $row->category ?? '—' }}
                            </td>
                            <td class="py-5">
                                <span class="inline-flex items-center rounded-lg bg-gray-100 px-3 py-1 text-[10px] font-bold uppercase text-gray-500">
                                    {{ $row->location }}
                                </span>
                            </td>
                            <td class="py-5 text-right font-black text-[#1e293b]">
                                {{ number_format($row->total_qty, 2) }}
                            </td>
                            <td class="py-5 text-center text-xs font-medium text-gray-400">
                                {{ $row->unit_of_measure ?: '—' }}
                            </td>
                            @if($canCost)
                                <td class="py-5 text-right font-bold text-[#e11d48]">
                                    @if($row->unit_cost)
                                        Bs. {{ number_format($row->unit_cost, 2) }}
                                    @else
                                        <span class="text-gray-300">—</span>
                                    @endif
                                </td>
                                <td class="py-5 pr-8 text-right font-black text-[#1e293b]">
                                    @if($row->unit_cost)
                                        Bs. {{ number_format($row->total_qty * $row->unit_cost, 2) }}
                                    @else
                                        <span class="text-gray-300">—</span>
                                    @endif
                                </td>
                            @else
                                <td class="py-5 pr-8"></td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $canCost ? 7 : 5 }}" class="py-20 text-center">
                                <p class="text-sm font-medium italic text-gray-400">No hay stock registrado con los filtros seleccionados.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($rows->hasPages())
            <div class="border-t border-gray-50 px-8 py-6">
                {{ $rows->links() }}
            </div>
        @endif
    </div>

    <div class="mt-8">
        <a href="{{ route('reports.index') }}"
           class="inline-flex items-center gap-2 rounded-2xl bg-gray-100 px-8 py-3 text-sm font-bold text-gray-500 transition-all hover:bg-gray-200 hover:text-[#1e293b]">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
            Volver a Reportes
        </a>
    </div>
@endsection

@push('scripts')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.min.css">
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>

<style>
    #ts-stock-product + .ts-wrapper .ts-control,
    .ts-wrapper.single .ts-control {
        border-radius: 0.75rem;
        border: 1px solid #e5e7eb;
        background: #fff;
        padding: 0 1rem;
        height: 2.75rem;
        font-size: 0.875rem;
        color: #374151;
        box-shadow: none;
        cursor: pointer;
    }
    .ts-wrapper.single .ts-control:focus-within { border-color: #9ca3af; }
    .ts-dropdown { border-radius: 0.75rem; border: 1px solid #f3f4f6; box-shadow: 0 10px 25px -5px rgb(0 0 0 / 0.1); z-index: 9999; }
    .ts-dropdown .option { padding: 0.6rem 1rem; font-size: 0.875rem; }
    .ts-dropdown .option.active { background: #f9fafb; color: #1e293b; }
    .ts-dropdown .option:hover { background: #f3f4f6; }
</style>

<script>
const __stockProducts = @json($productsForTs);
const __stockProductId = {{ $productId ? (int) $productId : 'null' }};

function stockProductFilter() {
    return {
        selectedId:   __stockProductId ? String(__stockProductId) : '',
        selectedName: '',
        barcodeQuery: '',
        barcodeNotFound: false,
        tsInstance: null,

        init() {
            const self = this;
            this.tsInstance = new TomSelect('#ts-stock-product', {
                valueField:  'id',
                labelField:  'name',
                searchField: ['name'],
                placeholder: 'Todos los productos...',
                options:     __stockProducts,
                maxOptions:  100,
                allowEmptyOption: true,
                render: {
                    option(data) { return `<div>${data.name}</div>`; },
                    item(data)   { return `<div>${data.name}</div>`; },
                },
                onChange(value) {
                    self.selectedId = value || '';
                    if (!value) { self.selectedName = ''; self.barcodeQuery = ''; return; }
                    const p = __stockProducts.find(p => String(p.id) === String(value));
                    if (!p) return;
                    self.selectedName = p.name;
                    self.barcodeQuery = p.barcodes.length > 0 ? p.barcodes[0] : '';
                    self.barcodeNotFound = false;
                },
            });

            // Pre-select from URL param
            if (__stockProductId) {
                this.tsInstance.setValue(String(__stockProductId), true);
                const p = __stockProducts.find(p => p.id === __stockProductId);
                if (p) {
                    this.selectedName = p.name;
                    this.barcodeQuery = p.barcodes.length > 0 ? p.barcodes[0] : '';
                }
            }
        },

        onBarcodeInput() {
            this.barcodeNotFound = false;
            const code = this.barcodeQuery.trim();
            if (!code) return;
            const found = __stockProducts.find(p => p.barcodes.some(b => b === code));
            if (found) this._select(found);
        },

        onBarcodeEnter() {
            const code = this.barcodeQuery.trim();
            if (!code) return;
            const found = __stockProducts.find(p => p.barcodes.some(b => b === code));
            if (found) { this._select(found); return; }
            this.barcodeNotFound = true;
        },

        _select(p) {
            this.selectedId   = String(p.id);
            this.selectedName = p.name;
            this.barcodeNotFound = false;
            if (this.tsInstance) this.tsInstance.setValue(String(p.id), true);
        },

        clear() {
            this.selectedId   = '';
            this.selectedName = '';
            this.barcodeQuery = '';
            this.barcodeNotFound = false;
            if (this.tsInstance) this.tsInstance.clear(true);
        },
    };
}
</script>
@endpush