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
                'search'          => $product?->name ?? '',
                'results'         => [],
                'showResults'     => false,
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
          x-data="{
              products:       window.__purchaseData.products,
              categories:     window.__purchaseData.categories,
              lines:          window.__purchaseData.lines,
              categoryFilter: '',
              priceMin:       '',
              priceMax:       '',
              init() {
                  if (this.lines.length === 0) this.addLine();
              },
              newLine() {
                  return {
                      product_id: '', product_name: '', use_batches: false,
                      search: '', results: [], showResults: false,
                      batch_code: '', expiration_date: '',
                      quantity: '', unit_cost: '',
                  };
              },
              addLine()    { this.lines.push(this.newLine()); },
              removeLine(i){ if (this.lines.length > 1) this.lines.splice(i, 1); },
              searchProduct(i) {
                  const q   = this.lines[i].search.toLowerCase().trim();
                  const cat = this.categoryFilter;
                  const min = this.priceMin !== '' ? parseFloat(this.priceMin) : null;
                  const max = this.priceMax !== '' ? parseFloat(this.priceMax) : null;
                  if (q.length < 1 && !cat && min === null && max === null) {
                      this.lines[i].results    = [];
                      this.lines[i].showResults = false;
                      return;
                  }
                  this.lines[i].results = this.products
                      .filter(p => {
                          const matchText = q.length < 1 ||
                              p.name.toLowerCase().includes(q) ||
                              p.barcodes.some(b => b.toLowerCase().includes(q));
                          const matchCat  = !cat || p.category_id == cat;
                          const matchMin  = min === null || (p.sale_price !== null && p.sale_price >= min);
                          const matchMax  = max === null || (p.sale_price !== null && p.sale_price <= max);
                          return matchText && matchCat && matchMin && matchMax;
                      })
                      .slice(0, 10);
                  this.lines[i].showResults = this.lines[i].results.length > 0;
              },
              selectProduct(i, p) {
                  this.lines[i].product_id   = p.id;
                  this.lines[i].product_name = p.name;
                  this.lines[i].use_batches  = p.use_batches;
                  this.lines[i].search       = p.name;
                  this.lines[i].showResults  = false;
                  this.lines[i].results      = [];
                  this.lines[i].batch_code      = '';
                  this.lines[i].expiration_date = '';
              },
              lineSubtotal(l) {
                  return ((parseFloat(l.quantity) || 0) * (parseFloat(l.unit_cost) || 0)).toFixed(2);
              },
              get total() {
                  return this.lines.reduce((s, l) =>
                      s + (parseFloat(l.quantity) || 0) * (parseFloat(l.unit_cost) || 0), 0
                  ).toFixed(2);
              },
          }"
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

        {{-- ── Filtros de producto ── --}}
        <div class="rounded-[2.5rem] border border-gray-100 bg-white px-8 py-5 shadow-sm">
            <p class="mb-3 text-[10px] font-black uppercase tracking-widest text-gray-400">Filtros de búsqueda</p>
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                <div class="sm:col-span-1">
                    <label class="mb-1 block text-xs font-bold text-[#1e293b]">Categoría</label>
                    <div class="relative">
                        <select x-model="categoryFilter"
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
                </div>
                <div>
                    <label class="mb-1 block text-xs font-bold text-[#1e293b]">Precio venta mín. (Bs)</label>
                    <input type="number" x-model="priceMin" min="0" step="0.01" placeholder="0.00"
                        class="h-9 w-full rounded-xl border border-gray-200 bg-gray-50 px-3 text-sm text-gray-700 focus:border-gray-400 focus:outline-none transition-all" />
                </div>
                <div>
                    <label class="mb-1 block text-xs font-bold text-[#1e293b]">Precio venta máx. (Bs)</label>
                    <input type="number" x-model="priceMax" min="0" step="0.01" placeholder="0.00"
                        class="h-9 w-full rounded-xl border border-gray-200 bg-gray-50 px-3 text-sm text-gray-700 focus:border-gray-400 focus:outline-none transition-all" />
                </div>
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
                            {{-- Búsqueda de producto --}}
                            <div>
                                <label class="mb-1.5 block text-sm font-bold text-[#1e293b]">
                                    Producto <span class="text-[#e11d48]">*</span>
                                </label>
                                <div class="relative" @click.outside="line.showResults = false">
                                    <input type="text"
                                        x-model="line.search"
                                        @input="searchProduct(i)"
                                        @focus="if(line.search.length > 0) searchProduct(i)"
                                        placeholder="Buscar por nombre o código de barras..."
                                        autocomplete="off"
                                        class="h-11 w-full rounded-xl border border-gray-200 bg-white px-4 text-sm text-gray-700 placeholder:text-gray-400 focus:border-gray-400 focus:outline-none transition-all" />

                                    <input type="hidden" :name="`lines[${i}][product_id]`" :value="line.product_id" />

                                    <div x-show="line.showResults"
                                         class="absolute top-full left-0 z-50 mt-1 max-h-60 w-full overflow-y-auto rounded-xl border border-gray-100 bg-white shadow-lg">
                                        <template x-for="result in line.results" :key="result.id">
                                            <button type="button"
                                                @click="selectProduct(i, result)"
                                                class="flex w-full items-center gap-3 px-4 py-2.5 text-left text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                                                <div class="flex-1 min-w-0">
                                                    <span class="block truncate font-semibold" x-text="result.name"></span>
                                                    <template x-if="result.sale_price !== null">
                                                        <span class="text-xs text-gray-400" x-text="'Bs ' + result.sale_price.toFixed(2)"></span>
                                                    </template>
                                                </div>
                                                <span x-show="result.use_batches"
                                                    class="inline-flex items-center rounded-lg bg-gray-100 px-2.5 py-0.5 text-[10px] font-bold uppercase text-gray-500">
                                                    PEPS
                                                </span>
                                            </button>
                                        </template>
                                    </div>
                                </div>

                                <p x-show="line.product_id && !line.showResults"
                                   class="mt-1.5 text-xs font-bold text-emerald-600">
                                    ✓ <span x-text="line.product_name"></span>
                                </p>
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