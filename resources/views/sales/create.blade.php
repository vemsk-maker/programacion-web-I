@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Punto de Venta" />

    {{-- Datos para Alpine.js --}}
    @php
        $productsJson = $products->map(fn ($p) => [
            'id'              => $p->id,
            'name'            => $p->name,
            'unit_of_measure' => $p->unit_of_measure,
            'use_batches'     => (bool) $p->use_batches,
            'sale_price'      => $p->sale_price !== null ? (float) $p->sale_price : null,
            'category_id'     => $p->category_id,
            'barcodes'        => $p->barcodes->pluck('barcode')->toArray(),
        ])->values()->toArray();
    @endphp

    <script>
        window.__posData = {
            products:   @json($productsJson),
            categories: @json($categories->map(fn($c) => ['id' => $c->id, 'name' => $c->name])->values()),
            locationId: '{{ $locations->count() === 1 ? $locations->first()->id : '' }}',
            storeUrl:   '{{ route('sales.store') }}',
            indexUrl:   '{{ route('sales.index') }}',
        };
    </script>

    <div x-data="posApp()" x-init="init()" class="space-y-5">

        {{-- Banner éxito --}}
        <div x-show="lastDoc" x-cloak
            class="flex items-center justify-between rounded-2xl bg-emerald-50 border border-emerald-100 px-6 py-4">
            <div class="flex items-center gap-3">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="text-emerald-600"><path d="M20 6L9 17l-5-5"/></svg>
                <p class="text-sm font-bold text-emerald-700">
                    Venta registrada — Recibo <span x-text="lastDoc" class="font-mono"></span>
                </p>
            </div>
            <div class="flex items-center gap-3">
                <a :href="lastDocUrl" target="_blank"
                    class="rounded-xl bg-emerald-600 px-4 py-2 text-xs font-bold text-white hover:bg-emerald-700 transition-all">
                    Ver / Imprimir
                </a>
                <button @click="lastDoc = null; lastDocUrl = null" class="text-emerald-400 hover:text-emerald-600 text-xl leading-none">✕</button>
            </div>
        </div>

        {{-- Banner error --}}
        <div x-show="errorMsg" x-cloak
            class="flex items-center gap-3 rounded-2xl bg-red-50 border border-red-100 px-6 py-4 text-sm font-bold text-[#e11d48]">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 6L6 18M6 6l12 12"/></svg>
            <span x-text="errorMsg"></span>
        </div>

        {{-- Layout POS: 3/5 izquierda + 2/5 derecha --}}
        <div class="grid gap-5 lg:grid-cols-5">

            {{-- ══ Columna izquierda ══ --}}
            <div class="space-y-5 lg:col-span-3">

                {{-- Selector de sucursal --}}
                @if($locations->count() > 1)
                    <div class="rounded-[2.5rem] border border-gray-100 bg-white p-6 shadow-sm">
                        <label class="mb-1.5 block text-sm font-bold text-[#1e293b]">
                            Sucursal de venta <span class="text-[#e11d48]">*</span>
                        </label>
                        <div class="relative">
                            <select x-model="locationId"
                                class="h-11 w-full appearance-none rounded-xl border border-gray-200 bg-white px-4 pr-10 text-sm text-gray-700 focus:border-gray-400 focus:outline-none transition-all">
                                <option value="">— Seleccionar sucursal —</option>
                                @foreach($locations as $loc)
                                    <option value="{{ $loc->id }}">{{ $loc->name }}</option>
                                @endforeach
                            </select>
                            <span class="pointer-events-none absolute top-1/2 right-4 -translate-y-1/2 text-gray-400">
                                <svg width="16" height="16" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M4.79175 7.396L10.0001 12.6043L15.2084 7.396" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            </span>
                        </div>
                    </div>
                @elseif($locations->count() === 1)
                    <div class="rounded-2xl border border-gray-100 bg-gray-50/50 px-6 py-3">
                        <p class="text-sm font-bold text-[#1e293b]">
                            Sucursal: <span class="text-gray-500">{{ $locations->first()->name }}</span>
                        </p>
                    </div>
                @endif

                {{-- Panel de búsqueda / selección --}}
                <div class="rounded-[2.5rem] border border-gray-100 bg-white p-6 shadow-sm space-y-4">

                    {{-- Filtro por categoría --}}
                    <div class="flex items-center gap-3">
                        <label class="shrink-0 text-[10px] font-black uppercase tracking-widest text-gray-400">Filtrar por categoría</label>
                        <div class="relative flex-1">
                            <select x-model="categoryFilter" @change="applyFilters()"
                                :disabled="!locationId"
                                class="h-9 w-full appearance-none rounded-xl border border-gray-200 bg-gray-50 px-3 pr-8 text-sm text-gray-700 focus:border-gray-400 focus:outline-none transition-all disabled:opacity-40">
                                <option value="">Todas las categorías</option>
                                <template x-for="cat in categories" :key="cat.id">
                                    <option :value="cat.id" x-text="cat.name"></option>
                                </template>
                            </select>
                            <span class="pointer-events-none absolute top-1/2 right-2.5 -translate-y-1/2 text-gray-400">
                                <svg width="12" height="12" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2"><path d="M4.79175 7.396L10.0001 12.6043L15.2084 7.396" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            </span>
                        </div>
                        <button type="button" x-show="categoryFilter" @click="categoryFilter = ''; applyFilters()"
                            class="shrink-0 text-xs font-bold text-gray-400 hover:text-[#e11d48] transition-colors">
                            Limpiar
                        </button>
                    </div>

                    {{-- Selector de producto (Tom Select) --}}
                    <div>
                        <label class="mb-1.5 block text-sm font-bold text-[#1e293b]">
                            Seleccionar producto
                        </label>
                        <select id="ts-product"></select>
                    </div>

                    {{-- Código de barras --}}
                    <div>
                        <label class="mb-1.5 block text-sm font-bold text-[#1e293b]">
                            Código de barras
                            <span class="ml-1 text-xs font-medium text-gray-400">(escaneo o manual)</span>
                        </label>
                        <div class="flex gap-2">
                            <input type="text"
                                x-ref="barcodeInput"
                                x-model="barcodeQuery"
                                @keydown.enter.prevent="onBarcodeEnter()"
                                @input="onBarcodeInput()"
                                placeholder="Escanee o escriba el código..."
                                autocomplete="off"
                                :disabled="!locationId"
                                class="h-11 flex-1 rounded-xl border border-gray-200 bg-white px-4 font-mono text-sm text-gray-700 placeholder:text-gray-400 focus:border-gray-400 focus:outline-none transition-all disabled:opacity-40" />
                            <button type="button" @click="addSelectedToCart()"
                                :disabled="!selectedProduct || !locationId"
                                class="h-11 rounded-xl bg-[#1e293b] px-5 text-sm font-bold text-white hover:bg-[#334155] transition-all active:scale-95 disabled:opacity-40 disabled:cursor-not-allowed">
                                + Agregar
                            </button>
                        </div>
                        <p x-show="barcodeNotFound" class="mt-1.5 text-xs font-bold text-[#e11d48]">
                            Código no encontrado en el catálogo.
                        </p>
                        <p class="mt-1.5 text-xs text-gray-400">
                            Presione <kbd class="rounded border border-gray-200 px-1 font-mono text-xs">Enter</kbd> en el código de barras para agregar directamente. Compatible con lectores USB.
                        </p>
                    </div>

                </div>

                {{-- Carrito --}}
                <div class="rounded-[2.5rem] border border-gray-100 bg-white shadow-sm">
                    <div class="flex items-center justify-between border-b border-gray-50 px-8 py-5">
                        <h3 class="text-lg font-bold text-[#1e293b]">
                            Carrito
                            <span x-show="cart.length > 0"
                                class="ml-2 inline-flex items-center rounded-lg bg-[#e11d48] px-2.5 py-0.5 text-xs font-black text-white"
                                x-text="cart.length"></span>
                        </h3>
                        <button type="button" @click="clearCart()"
                            x-show="cart.length > 0"
                            class="text-xs font-bold text-gray-400 hover:text-[#e11d48] transition-colors">
                            Limpiar carrito
                        </button>
                    </div>

                    {{-- Estado vacío --}}
                    <div x-show="cart.length === 0"
                        class="flex flex-col items-center justify-center py-16 text-center">
                        <svg class="mb-3 h-14 w-14 text-gray-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                        <p class="text-sm font-bold text-gray-400">El carrito está vacío</p>
                        <p class="mt-1 text-xs text-gray-300">Seleccione un producto para comenzar</p>
                    </div>

                    {{-- Líneas del carrito --}}
                    <div x-show="cart.length > 0">
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="text-[10px] font-bold uppercase tracking-widest text-gray-400">
                                        <th class="pb-3 pl-8 pt-4 text-left">Producto</th>
                                        <th class="pb-3 pt-4 text-center">UM</th>
                                        <th class="pb-3 pt-4 text-center w-24">Cant.</th>
                                        <th class="pb-3 pt-4 text-center w-28">Precio</th>
                                        <th class="pb-3 pt-4 text-right">Subtotal</th>
                                        <th class="pb-3 pt-4 w-10"></th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-50 border-t border-gray-50">
                                    <template x-for="(line, i) in cart" :key="i">
                                        <tr class="hover:bg-gray-50/50 transition-colors">
                                            <td class="py-4 pl-8">
                                                <p class="font-bold text-[#1e293b]" x-text="line.name"></p>
                                                <p x-show="line.use_batches" class="text-xs font-bold text-amber-500">Lote automático (PEPS)</p>
                                                <p x-show="parseFloat(line.qty) > line.stock && line.stock >= 0"
                                                    class="text-xs font-bold text-[#e11d48]">
                                                    ⚠ Supera stock (<span x-text="line.stock"></span> disp.)
                                                </p>
                                            </td>
                                            <td class="py-4 text-center text-xs font-medium text-gray-400" x-text="line.unit || '—'"></td>
                                            <td class="py-4 px-2">
                                                <input type="number" step="0.01" min="0.01"
                                                    x-model="line.qty"
                                                    class="h-9 w-full rounded-xl border border-gray-200 bg-white px-2 text-center text-sm font-bold text-[#1e293b] focus:border-gray-400 focus:outline-none transition-all" />
                                            </td>
                                            <td class="py-4 px-2">
                                                <div class="relative">
                                                    <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-xs font-bold text-gray-400">Bs</span>
                                                    <input type="number" step="0.01" min="0"
                                                        x-model="line.price"
                                                        class="h-9 w-full rounded-xl border border-gray-200 bg-white pl-8 pr-2 text-right text-sm font-bold text-[#1e293b] focus:border-gray-400 focus:outline-none transition-all" />
                                                </div>
                                            </td>
                                            <td class="py-4 pr-2 text-right font-black text-[#1e293b]">
                                                <span x-text="fmt((parseFloat(line.qty)||0) * (parseFloat(line.price)||0))"></span>
                                            </td>
                                            <td class="py-4 pr-4">
                                                <button type="button" @click="cart.splice(i, 1)"
                                                    class="flex h-7 w-7 items-center justify-center rounded-full text-gray-300 hover:bg-red-50 hover:text-[#e11d48] transition-all">✕</button>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ══ Columna derecha ══ --}}
            <div class="lg:col-span-2">
                <div class="sticky top-5 space-y-5">

                    {{-- Datos del cliente --}}
                    <div class="rounded-[2.5rem] border border-gray-100 bg-white p-6 shadow-sm">
                        <p class="mb-4 text-[10px] font-bold uppercase tracking-widest text-gray-400">
                            Cliente <span class="normal-case font-medium text-gray-300">(opcional)</span>
                        </p>
                        <div class="space-y-4">
                            <div>
                                <label class="mb-1.5 block text-sm font-bold text-[#1e293b]">Nombre</label>
                                <input type="text" x-model="clientName" placeholder="Nombre del cliente..."
                                    class="h-11 w-full rounded-xl border border-gray-200 bg-white px-4 text-sm text-gray-700 placeholder:text-gray-400 focus:border-gray-400 focus:outline-none transition-all" />
                            </div>
                            <div>
                                <label class="mb-1.5 block text-sm font-bold text-[#1e293b]">NIT / CI</label>
                                <input type="text" x-model="clientNit" placeholder="NIT o carnet..."
                                    class="h-11 w-full rounded-xl border border-gray-200 bg-white px-4 text-sm text-gray-700 placeholder:text-gray-400 focus:border-gray-400 focus:outline-none transition-all" />
                            </div>
                        </div>
                    </div>

                    {{-- Total + confirmar --}}
                    <div class="rounded-[2.5rem] border border-gray-100 bg-white p-6 shadow-sm">
                        <div class="mb-5 flex items-baseline justify-between">
                            <span class="text-[10px] font-bold uppercase tracking-widest text-gray-400">Total</span>
                            <span class="font-mono text-3xl font-black text-[#1e293b]"
                                x-text="fmt(cart.reduce((s,l) => s + (parseFloat(l.qty)||0)*(parseFloat(l.price)||0), 0))"></span>
                        </div>

                        <div x-show="cart.length > 0" class="mb-5 space-y-1.5 border-t border-gray-50 pt-4">
                            <template x-for="line in cart" :key="line.product_id">
                                <div class="flex justify-between text-xs">
                                    <span class="font-medium text-gray-500" x-text="`${line.name} × ${parseFloat(line.qty)||0}`"></span>
                                    <span class="font-bold text-gray-700" x-text="fmt((parseFloat(line.qty)||0)*(parseFloat(line.price)||0))"></span>
                                </div>
                            </template>
                        </div>

                        <button type="button" @click="confirmSale()"
                            :disabled="cart.length === 0 || !locationId || submitting"
                            class="relative w-full rounded-xl bg-[#e11d48] px-5 py-4 text-base font-black text-white shadow-md transition-all hover:bg-[#be123c] active:scale-95 disabled:opacity-40 disabled:cursor-not-allowed disabled:active:scale-100">
                            <span x-show="!submitting">Confirmar Venta</span>
                            <span x-show="submitting" class="flex items-center justify-center gap-2">
                                <span class="inline-block h-4 w-4 animate-spin rounded-full border-2 border-white border-t-transparent"></span>
                                Procesando...
                            </span>
                        </button>

                        <div x-show="!locationId" class="mt-3 text-center text-xs font-bold text-amber-500">
                            Seleccione una sucursal para habilitar la venta
                        </div>
                        <div x-show="locationId && cart.length === 0" class="mt-3 text-center text-xs font-medium text-gray-400">
                            Agregue productos al carrito para continuar
                        </div>
                    </div>

                    <a href="{{ route('sales.index') }}"
                        class="flex h-11 w-full items-center justify-center rounded-xl border border-gray-200 bg-white text-sm font-bold text-gray-500 hover:bg-gray-50 hover:text-[#1e293b] transition-all">
                        Ver Historial de Ventas
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
{{-- Tom Select --}}
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.min.css">
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>

<style>
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
    .ts-dropdown { border-radius: 0.75rem; border: 1px solid #f3f4f6; box-shadow: 0 10px 25px -5px rgb(0 0 0 / 0.1); }
    .ts-dropdown .option { padding: 0.6rem 1rem; font-size: 0.875rem; }
    .ts-dropdown .option.active { background: #f9fafb; color: #1e293b; }
    .ts-dropdown .option:hover { background: #f3f4f6; }
    .ts-wrapper.disabled .ts-control { opacity: 0.4; cursor: not-allowed; }
</style>

<script>
function posApp() {
    return {
        // ─── State ───────────────────────────────────────────────────────
        allProducts:    window.__posData.products,
        categories:     window.__posData.categories,
        locationId:     window.__posData.locationId,
        storeUrl:       window.__posData.storeUrl,

        categoryFilter: '',

        selectedProduct:  null,
        barcodeQuery:     '',
        barcodeNotFound:  false,

        cart:       [],
        clientName: '',
        clientNit:  '',

        submitting: false,
        lastDoc:    null,
        lastDocUrl: null,
        errorMsg:   null,

        tsInstance: null,
        csrfToken:  '',

        // ─── Init ─────────────────────────────────────────────────────────
        init() {
            this.csrfToken = document.querySelector('meta[name=csrf-token]')?.content ?? '';
            this.$nextTick(() => {
                this.initTomSelect();
                this.$refs.barcodeInput?.focus();
            });
            // Habilitar / deshabilitar Tom Select cuando cambia la sucursal
            this.$watch('locationId', val => {
                if (!this.tsInstance) return;
                if (val) {
                    this.tsInstance.enable();
                } else {
                    this.tsInstance.disable();
                    this.tsInstance.clear(true);
                    this.selectedProduct = null;
                    this.barcodeQuery    = '';
                }
            });
        },

        initTomSelect() {
            const self = this;
            if (this.tsInstance) { this.tsInstance.destroy(); }

            this.tsInstance = new TomSelect('#ts-product', {
                valueField:   'id',
                labelField:   'name',
                searchField:  ['name'],
                placeholder:  'Escriba nombre del producto...',
                options:      this.filteredProducts(),
                maxOptions:   50,
                render: {
                    option: function(data) {
                        const price = data.sale_price !== null
                            ? `<span class="text-xs font-bold text-gray-500 ml-2">Bs ${parseFloat(data.sale_price).toFixed(2)}</span>`
                            : '';
                        const peps = data.use_batches
                            ? `<span class="ml-1 text-[10px] font-bold uppercase text-amber-600 bg-amber-50 px-1.5 py-0.5 rounded">PEPS</span>`
                            : '';
                        return `<div class="flex items-center gap-1">${data.name}${peps}${price}</div>`;
                    },
                    item: function(data) {
                        return `<div>${data.name}</div>`;
                    },
                },
                onChange(value) {
                    if (!value) { self.selectedProduct = null; self.barcodeQuery = ''; return; }
                    const p = self.allProducts.find(p => p.id == value);
                    if (!p) return;
                    self.selectedProduct = p;
                    // Fill barcode with first barcode of this product
                    self.barcodeQuery = p.barcodes.length > 0 ? p.barcodes[0] : '';
                    self.barcodeNotFound = false;
                },
            });

            // Disable if no location
            if (!this.locationId) this.tsInstance.disable();
        },

        filteredProducts() {
            return this.allProducts.filter(p => {
                if (this.categoryFilter && p.category_id != this.categoryFilter) return false;
                return true;
            });
        },

        applyFilters() {
            if (!this.tsInstance) return;
            const filtered = this.filteredProducts();
            this.tsInstance.clearOptions();
            this.tsInstance.addOptions(filtered);
            this.tsInstance.refreshOptions(false);
        },

        // ─── Barcode input ────────────────────────────────────────────────
        onBarcodeInput() {
            this.barcodeNotFound = false;
            const code = this.barcodeQuery.trim();
            if (!code) { return; }
            // Find product matching this barcode
            const found = this.allProducts.find(p =>
                p.barcodes.some(b => b === code)
            );
            if (found) {
                this.selectedProduct = found;
                if (this.tsInstance) {
                    this.tsInstance.setValue(found.id, true); // true = silent (no onChange loop)
                }
            }
        },

        onBarcodeEnter() {
            const code = this.barcodeQuery.trim();
            if (!code) return;
            // Try to match barcode first
            const byBarcode = this.allProducts.find(p => p.barcodes.some(b => b === code));
            if (byBarcode) {
                this.selectedProduct = byBarcode;
                if (this.tsInstance) this.tsInstance.setValue(byBarcode.id, true);
                this.addSelectedToCart();
                return;
            }
            this.barcodeNotFound = true;
        },

        // ─── Cart ─────────────────────────────────────────────────────────
        addSelectedToCart() {
            if (!this.selectedProduct || !this.locationId) return;
            const p   = this.selectedProduct;
            const idx = this.cart.findIndex(l => l.product_id === p.id);
            if (idx !== -1) {
                this.cart[idx].qty = parseFloat(this.cart[idx].qty) + 1;
            } else {
                this.cart.push({
                    product_id: p.id,
                    name:       p.name,
                    unit:       p.unit_of_measure || '',
                    use_batches:p.use_batches,
                    stock:      0,
                    qty:        1,
                    price:      p.sale_price !== null ? p.sale_price : 0,
                });
            }
            // Reset selector and barcode for next scan
            this.selectedProduct = null;
            this.barcodeQuery    = '';
            this.barcodeNotFound = false;
            if (this.tsInstance) this.tsInstance.clear(true);
            this.$nextTick(() => this.$refs.barcodeInput?.focus());
        },

        clearCart() {
            this.cart       = [];
            this.clientName = '';
            this.clientNit  = '';
            this.errorMsg   = null;
        },

        // ─── Sale submission ──────────────────────────────────────────────
        async confirmSale() {
            if (this.cart.length === 0 || !this.locationId || this.submitting) return;
            this.submitting = true;
            this.errorMsg   = null;
            try {
                const r = await fetch(this.storeUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept':       'application/json',
                        'X-CSRF-TOKEN': this.csrfToken,
                    },
                    body: JSON.stringify({
                        location_id: parseInt(this.locationId),
                        client_name: this.clientName || null,
                        client_nit:  this.clientNit  || null,
                        lines: this.cart.map(l => ({
                            product_id: l.product_id,
                            quantity:   parseFloat(l.qty)   || 0,
                            unit_price: parseFloat(l.price) || 0,
                        })),
                    }),
                });
                const data = await r.json();
                if (r.ok && data.success) {
                    this.lastDoc    = data.doc_number;
                    this.lastDocUrl = data.show_url;
                    this.cart       = [];
                    this.clientName = '';
                    this.clientNit  = '';
                    if (this.tsInstance) this.tsInstance.clear(true);
                    this.barcodeQuery = '';
                    this.$nextTick(() => this.$refs.barcodeInput?.focus());
                } else if (r.status === 422 && data.errors) {
                    this.errorMsg = Object.values(data.errors).flat().join(' | ');
                } else {
                    this.errorMsg = data.message ?? 'Error al procesar la venta.';
                }
            } catch(e) {
                this.errorMsg = 'Error de conexión. Intente nuevamente.';
            }
            this.submitting = false;
        },

        fmt(v) { return 'Bs. ' + (parseFloat(v) || 0).toFixed(2); },
    };
}
</script>
@endpush
