@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Nueva Compra" />

    @php
        $oldLines = old('lines', []);
        $productsById = $products->keyBy('id');
        $initialLines = collect($oldLines)->map(function ($line) use ($productsById) {
            $product = $productsById->get($line['product_id'] ?? null);
            return [
                'product_id'      => $line['product_id'] ?? '',
                'product_name'    => $product?->name ?? '',
                'use_batches'     => (bool) ($product?->use_batches ?? false),
                'barcodeQuery'    => $product?->barcodes?->first()?->barcode ?? '',
                'barcodeNotFound' => false,
                'batch_code'      => $line['batch_code'] ?? '',
                'expiration_date' => $line['expiration_date'] ?? '',
                'quantity'        => $line['quantity'] ?? '',
                'unit_cost'       => $line['unit_cost'] ?? '',
            ];
        })->values()->toArray();

        $productsJson = $products->map(fn ($p) => [
            'id'          => $p->id,
            'name'        => $p->name,
            'use_batches' => (bool) $p->use_batches,
            'barcodes'    => $p->barcodes->pluck('barcode')->toArray(),
            'category_id' => $p->category_id,
            'sale_price'  => $p->sale_price !== null ? (float) $p->sale_price : null,
        ])->values()->toArray();
    @endphp

    <script>
        window.__purchaseData = {
            products:   @json($productsJson),
            categories: @json($categories->map(fn($c) => ['id' => $c->id, 'name' => $c->name])->values()),
            lines:      @json($initialLines),
        };
    </script>

    {{-- Flash / validation errors --}}
    @if(session('error'))
        <div class="mb-6 flex items-center gap-3 rounded-2xl bg-red-50 border border-red-100 px-5 py-3 text-sm font-bold text-[#e11d48]">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            {{ session('error') }}
        </div>
    @endif

    @php
        $lineErrorKeys = collect($errors->keys())->filter(fn ($k) => str_starts_with($k, 'lines.'));
    @endphp
    @if($lineErrorKeys->isNotEmpty())
        <div class="mb-6 rounded-2xl bg-red-50 border border-red-100 px-5 py-4 text-sm text-[#e11d48]">
            <p class="mb-2 font-bold">Revise los errores en las líneas de producto:</p>
            <ul class="list-inside list-disc space-y-0.5 font-medium">
                @foreach($lineErrorKeys as $key)
                    @foreach($errors->get($key) as $msg)
                        <li>{{ $msg }}</li>
                    @endforeach
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('inventory.purchases.store') }}"
          x-data="purchaseForm()"
          class="space-y-6">
        @csrf

        {{-- ── Cabecera ── --}}
        <div class="rounded-[2.5rem] border border-gray-100 bg-white p-8 shadow-sm">
            <div class="mb-6 border-b border-gray-50 pb-6">
                <h3 class="text-2xl font-bold text-[#1e293b]">Nueva Compra</h3>
                <p class="text-sm text-gray-500">Complete los datos para registrar el ingreso de mercadería</p>
            </div>

            <div class="grid gap-6 md:grid-cols-2">
                {{-- Datos de la Compra --}}
                <div class="space-y-5">
                    <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400">Datos de la Compra</p>

                    <div>
                        <label class="mb-1.5 block text-sm font-bold text-[#1e293b]">
                            Proveedor <span class="text-[#e11d48]">*</span>
                        </label>
                        <div class="relative">
                            <select name="supplier_id"
                                class="h-11 w-full appearance-none rounded-xl border border-gray-200 bg-white px-4 pr-10 text-sm text-gray-700 focus:border-gray-400 focus:outline-none transition-all {{ $errors->has('supplier_id') ? 'border-[#e11d48]' : '' }}">
                                <option value="">— Seleccionar proveedor —</option>
                                @foreach($suppliers as $supplier)
                                    <option value="{{ $supplier->id }}" {{ old('supplier_id') == $supplier->id ? 'selected' : '' }}>
                                        {{ $supplier->name }}
                                    </option>
                                @endforeach
                            </select>
                            <span class="pointer-events-none absolute top-1/2 right-4 -translate-y-1/2 text-gray-400">
                                <svg width="16" height="16" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M4.79175 7.396L10.0001 12.6043L15.2084 7.396" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            </span>
                        </div>
                        @error('supplier_id')
                            <p class="mt-1 text-xs font-bold text-[#e11d48]">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-bold text-[#1e293b]">
                            Ubicación de destino <span class="text-[#e11d48]">*</span>
                        </label>
                        <div class="relative">
                            <select name="location_id"
                                class="h-11 w-full appearance-none rounded-xl border border-gray-200 bg-white px-4 pr-10 text-sm text-gray-700 focus:border-gray-400 focus:outline-none transition-all {{ $errors->has('location_id') ? 'border-[#e11d48]' : '' }}">
                                <option value="">— Seleccionar ubicación —</option>
                                @foreach($locations as $location)
                                    <option value="{{ $location->id }}" {{ old('location_id') == $location->id ? 'selected' : '' }}>
                                        {{ $location->name }}
                                        ({{ $location->type->value === 'warehouse' ? 'Almacén' : 'Tienda' }})
                                    </option>
                                @endforeach
                            </select>
                            <span class="pointer-events-none absolute top-1/2 right-4 -translate-y-1/2 text-gray-400">
                                <svg width="16" height="16" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M4.79175 7.396L10.0001 12.6043L15.2084 7.396" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            </span>
                        </div>
                        @error('location_id')
                            <p class="mt-1 text-xs font-bold text-[#e11d48]">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Información Adicional --}}
                <div class="space-y-5">
                    <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400">Información Adicional</p>

                    <div>
                        <label class="mb-1.5 block text-sm font-bold text-[#1e293b]">
                            Nro. de Factura / Referencia
                        </label>
                        <input type="text" name="reference_doc" value="{{ old('reference_doc') }}"
                            placeholder="Ej: FAC-001234"
                            class="h-11 w-full rounded-xl border border-gray-200 bg-white px-4 text-sm text-gray-700 placeholder:text-gray-400 focus:border-gray-400 focus:outline-none transition-all" />
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-bold text-[#1e293b]">Notas</label>
                        <textarea name="notes" rows="3" placeholder="Observaciones opcionales..."
                            class="w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-700 placeholder:text-gray-400 focus:border-gray-400 focus:outline-none transition-all">{{ old('notes') }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Filtro por categoría ── --}}
        <div class="rounded-[2.5rem] border border-gray-100 bg-white px-8 py-5 shadow-sm">
            <div class="flex items-center gap-3">
                <label class="shrink-0 text-[10px] font-black uppercase tracking-widest text-gray-400">Filtrar por categoría</label>
                <div class="relative flex-1 max-w-xs">
                    <select x-model="categoryFilter" @change="applyFilter()"
                        class="h-9 w-full appearance-none rounded-xl border border-gray-200 bg-gray-50 px-3 pr-8 text-sm text-gray-700 focus:border-gray-400 focus:outline-none transition-all">
                        <option value="">Todas las categorías</option>
                        <template x-for="cat in categories" :key="cat.id">
                            <option :value="cat.id" x-text="cat.name"></option>
                        </template>
                    </select>
                    <span class="pointer-events-none absolute top-1/2 right-2.5 -translate-y-1/2 text-gray-400">
                        <svg width="12" height="12" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2"><path d="M4.79175 7.396L10.0001 12.6043L15.2084 7.396" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </span>
                </div>
                <button type="button" x-show="categoryFilter" @click="categoryFilter = ''; applyFilter()"
                    class="shrink-0 text-xs font-bold text-gray-400 hover:text-[#e11d48] transition-colors">
                    Limpiar
                </button>
            </div>
        </div>

        {{-- ── Líneas de productos ── --}}
        <div class="rounded-[2.5rem] border border-gray-100 bg-white shadow-sm">

            <div class="flex items-center justify-between border-b border-gray-50 px-8 py-6">
                <div>
                    <h3 class="text-xl font-bold text-[#1e293b]">Productos</h3>
                    <p class="text-sm text-gray-500">Agregue los ítems de esta compra</p>
                </div>
                <button type="button" @click="addLine()"
                    class="flex h-11 items-center gap-2 rounded-xl bg-[#1e293b] px-6 text-sm font-bold text-white hover:bg-[#334155] transition-all active:scale-95">
                    <span class="text-lg">+</span> Agregar producto
                </button>
            </div>

            <div class="divide-y divide-gray-50">
                <template x-for="(line, i) in lines" :key="i">
                    <div class="relative px-8 py-6">

                        <div class="mb-4 flex items-center justify-between">
                            <span class="text-[10px] font-bold uppercase tracking-widest text-gray-400"
                                  x-text="`Producto #${i + 1}`"></span>
                            <button type="button" @click="removeLine(i)"
                                class="flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-bold text-gray-400 hover:bg-red-50 hover:text-[#e11d48] transition-all"
                                :class="lines.length === 1 ? 'opacity-30 cursor-not-allowed' : ''"
                                :disabled="lines.length === 1">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                Eliminar
                            </button>
                        </div>

                        <div class="grid gap-5">
                            {{-- Selector de producto (Tom Select) + Código de barras --}}
                            <div class="grid gap-4 sm:grid-cols-2">
                                <div>
                                    <label class="mb-1.5 block text-sm font-bold text-[#1e293b]">
                                        Producto <span class="text-[#e11d48]">*</span>
                                    </label>
                                    <select :id="'ts-line-' + i" class="ts-purchase-line"></select>
                                    <input type="hidden" :name="`lines[${i}][product_id]`" :value="line.product_id" />
                                </div>
                                <div>
                                    <label class="mb-1.5 block text-sm font-bold text-[#1e293b]">
                                        Código de barras
                                        <span class="ml-1 text-xs font-medium text-gray-400">(escaneo o manual)</span>
                                    </label>
                                    <input type="text"
                                        :id="'bc-line-' + i"
                                        x-model="line.barcodeQuery"
                                        @keydown.enter.prevent="onBarcodeEnterLine(i)"
                                        @input="onBarcodeInputLine(i)"
                                        placeholder="Escanee o escriba el código..."
                                        autocomplete="off"
                                        class="h-11 w-full rounded-xl border border-gray-200 bg-white px-4 font-mono text-sm text-gray-700 placeholder:text-gray-400 focus:border-gray-400 focus:outline-none transition-all" />
                                    <p x-show="line.barcodeNotFound" class="mt-1 text-xs font-bold text-[#e11d48]">
                                        Código no encontrado en el catálogo.
                                    </p>
                                    <p class="mt-1 text-[11px] text-gray-400" x-show="!line.barcodeNotFound">
                                        Presione <kbd class="rounded border border-gray-200 px-1 font-mono text-[10px]">Enter</kbd> para seleccionar por barcode. Compatible con lectores USB.
                                    </p>
                                </div>
                            </div>

                            {{-- Campos de lote --}}
                            <template x-if="line.use_batches">
                                <div class="grid gap-4 sm:grid-cols-2">
                                    <div>
                                        <label class="mb-1.5 block text-sm font-bold text-[#1e293b]">
                                            Código de Lote <span class="text-[#e11d48]">*</span>
                                        </label>
                                        <input type="text"
                                            :name="`lines[${i}][batch_code]`"
                                            x-model="line.batch_code"
                                            placeholder="Ej: LOT-2024-001"
                                            class="h-11 w-full rounded-xl border border-gray-200 bg-white px-4 text-sm text-gray-700 placeholder:text-gray-400 focus:border-gray-400 focus:outline-none transition-all" />
                                    </div>
                                    <div>
                                        <label class="mb-1.5 block text-sm font-bold text-[#1e293b]">
                                            Fecha de Vencimiento <span class="text-[#e11d48]">*</span>
                                        </label>
                                        <input type="date"
                                            :name="`lines[${i}][expiration_date]`"
                                            x-model="line.expiration_date"
                                            class="h-11 w-full rounded-xl border border-gray-200 bg-white px-4 text-sm text-gray-700 focus:border-gray-400 focus:outline-none transition-all" />
                                    </div>
                                </div>
                            </template>

                            <template x-if="!line.use_batches">
                                <div>
                                    <input type="hidden" :name="`lines[${i}][batch_code]`" value="" />
                                    <input type="hidden" :name="`lines[${i}][expiration_date]`" value="" />
                                </div>
                            </template>

                            {{-- Cantidad, Costo, Subtotal --}}
                            <div class="grid gap-4 sm:grid-cols-3">
                                <div>
                                    <label class="mb-1.5 block text-sm font-bold text-[#1e293b]">
                                        Cantidad <span class="text-[#e11d48]">*</span>
                                    </label>
                                    <input type="number" step="0.01" min="0.01"
                                        :name="`lines[${i}][quantity]`"
                                        x-model.number="line.quantity"
                                        placeholder="0.00"
                                        class="h-11 w-full rounded-xl border border-gray-200 bg-white px-4 text-sm text-gray-700 focus:border-gray-400 focus:outline-none transition-all" />
                                </div>
                                <div>
                                    <label class="mb-1.5 block text-sm font-bold text-[#1e293b]">
                                        Costo Unitario (Bs.) <span class="text-[#e11d48]">*</span>
                                    </label>
                                    <input type="number" step="0.01" min="0"
                                        :name="`lines[${i}][unit_cost]`"
                                        x-model.number="line.unit_cost"
                                        placeholder="0.00"
                                        class="h-11 w-full rounded-xl border border-gray-200 bg-white px-4 text-sm text-gray-700 focus:border-gray-400 focus:outline-none transition-all" />
                                </div>
                                <div>
                                    <label class="mb-1.5 block text-sm font-bold text-[#1e293b]">Subtotal</label>
                                    <div class="flex h-11 items-center rounded-xl border border-gray-100 bg-gray-50/50 px-4 text-sm font-bold text-[#1e293b]">
                                        Bs. <span class="ml-1" x-text="lineSubtotal(line)">0.00</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            {{-- Total --}}
            <div class="flex items-center justify-end gap-4 rounded-b-[2.5rem] border-t border-gray-50 bg-gray-50/30 px-8 py-5">
                <span class="text-[10px] font-bold uppercase tracking-widest text-gray-400">Total de la compra:</span>
                <span class="font-mono text-2xl font-black text-[#1e293b]">
                    Bs. <span x-text="total">0.00</span>
                </span>
            </div>
        </div>

        {{-- ── Acciones ── --}}
        <div class="flex items-center gap-3">
            <button type="submit"
                class="flex h-11 items-center gap-2 rounded-xl bg-[#e11d48] px-8 text-sm font-bold text-white shadow-md transition-all hover:bg-[#be123c] active:scale-95">
                Registrar Compra
            </button>
            <a href="{{ route('inventory.purchases.index') }}"
               class="flex h-11 items-center rounded-xl bg-gray-100 px-8 text-sm font-bold text-gray-500 hover:bg-gray-200 hover:text-[#1e293b] transition-all">
                Cancelar
            </a>
        </div>
    </form>
@endsection

@push('scripts')
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
    .ts-dropdown { border-radius: 0.75rem; border: 1px solid #f3f4f6; box-shadow: 0 10px 25px -5px rgb(0 0 0 / 0.1); z-index: 9999; }
    .ts-dropdown .option { padding: 0.6rem 1rem; font-size: 0.875rem; }
    .ts-dropdown .option.active { background: #f9fafb; color: #1e293b; }
    .ts-dropdown .option:hover { background: #f3f4f6; }
</style>

<script>
const __allProducts   = window.__purchaseData.products;
const __allCategories = window.__purchaseData.categories;

// Tom Select instances keyed by line index
const __tsInstances = {};

function purchaseForm() {
    return {
        products:       __allProducts,
        categories:     __allCategories,
        lines:          window.__purchaseData.lines,
        categoryFilter: '',

        init() {
            if (this.lines.length === 0) this.addLine();
            this.$nextTick(() => {
                this.lines.forEach((_, i) => this.initTs(i));
            });
        },

        newLine() {
            return {
                product_id: '', product_name: '', use_batches: false,
                batch_code: '', expiration_date: '',
                quantity: '', unit_cost: '',
                barcodeQuery: '', barcodeNotFound: false,
            };
        },

        onBarcodeInputLine(i) {
            this.lines[i].barcodeNotFound = false;
            const code = this.lines[i].barcodeQuery.trim();
            if (!code) return;
            const found = __allProducts.find(p => p.barcodes.some(b => b === code));
            if (found) {
                this._selectProductOnLine(i, found);
            }
        },

        onBarcodeEnterLine(i) {
            const code = this.lines[i].barcodeQuery.trim();
            if (!code) return;
            const found = __allProducts.find(p => p.barcodes.some(b => b === code));
            if (found) {
                this._selectProductOnLine(i, found);
                return;
            }
            this.lines[i].barcodeNotFound = true;
        },

        _selectProductOnLine(i, p) {
            this.lines[i].product_id   = p.id;
            this.lines[i].product_name = p.name;
            this.lines[i].use_batches  = p.use_batches;
            this.lines[i].barcodeNotFound = false;
            if (__tsInstances[i]) __tsInstances[i].setValue(p.id, true);
        },

        addLine() {
            this.lines.push(this.newLine());
            this.$nextTick(() => this.initTs(this.lines.length - 1));
        },

        removeLine(i) {
            if (this.lines.length <= 1) return;
            if (__tsInstances[i]) { __tsInstances[i].destroy(); delete __tsInstances[i]; }
            this.lines.splice(i, 1);
            // Re-index remaining instances
            this.$nextTick(() => {
                const newInstances = {};
                this.lines.forEach((_, idx) => {
                    const el = document.getElementById('ts-line-' + idx);
                    if (el && !el.tomselect) this.initTs(idx);
                    else if (el?.tomselect) newInstances[idx] = el.tomselect;
                });
            });
        },

        filteredProducts() {
            if (!this.categoryFilter) return this.products;
            return this.products.filter(p => p.category_id == this.categoryFilter);
        },

        applyFilter() {
            const filtered = this.filteredProducts();
            Object.keys(__tsInstances).forEach(i => {
                const ts = __tsInstances[i];
                const current = this.lines[i]?.product_id;
                ts.clearOptions();
                ts.addOptions(filtered);
                ts.refreshOptions(false);
                // Keep current selection if still in filtered list
                if (current && filtered.some(p => p.id == current)) {
                    ts.setValue(current, true);
                } else if (current) {
                    ts.clear(true);
                    this.lines[i].product_id   = '';
                    this.lines[i].product_name = '';
                    this.lines[i].use_batches  = false;
                }
            });
        },

        initTs(i) {
            const self = this;
            const el = document.getElementById('ts-line-' + i);
            if (!el || el.tomselect) return;

            const ts = new TomSelect(el, {
                valueField:  'id',
                labelField:  'name',
                searchField: ['name'],
                placeholder: 'Buscar por nombre...',
                options:     this.filteredProducts(),
                maxOptions:  50,
                render: {
                    option(data) {
                        const price = data.sale_price !== null
                            ? `<span style="font-size:11px;color:#9ca3af;margin-left:6px">Bs ${parseFloat(data.sale_price).toFixed(2)}</span>`
                            : '';
                        const peps = data.use_batches
                            ? `<span style="font-size:10px;font-weight:700;color:#d97706;background:#fef3c7;padding:1px 6px;border-radius:4px;margin-left:4px">PEPS</span>`
                            : '';
                        return `<div style="display:flex;align-items:center;gap:4px">${data.name}${peps}${price}</div>`;
                    },
                    item(data) { return `<div>${data.name}</div>`; },
                },
                onChange(value) {
                    if (!value) {
                        self.lines[i].product_id   = '';
                        self.lines[i].product_name = '';
                        self.lines[i].use_batches  = false;
                        self.lines[i].barcodeQuery = '';
                        return;
                    }
                    const p = __allProducts.find(p => p.id == value);
                    if (!p) return;
                    self.lines[i].product_id      = p.id;
                    self.lines[i].product_name    = p.name;
                    self.lines[i].use_batches     = p.use_batches;
                    self.lines[i].barcodeQuery    = p.barcodes.length > 0 ? p.barcodes[0] : '';
                    self.lines[i].barcodeNotFound = false;
                    self.lines[i].batch_code      = '';
                    self.lines[i].expiration_date = '';
                },
            });

            // Pre-select if line already has a product (old() repopulation)
            if (this.lines[i]?.product_id) {
                ts.setValue(this.lines[i].product_id, true);
            }

            __tsInstances[i] = ts;
        },

        lineSubtotal(l) {
            return ((parseFloat(l.quantity) || 0) * (parseFloat(l.unit_cost) || 0)).toFixed(2);
        },
        get total() {
            return this.lines.reduce((s, l) =>
                s + (parseFloat(l.quantity) || 0) * (parseFloat(l.unit_cost) || 0), 0
            ).toFixed(2);
        },
    };
}
</script>
@endpush