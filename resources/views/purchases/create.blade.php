@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Nueva Compra" />

    @php
        // Build Alpine initial lines from old() after a validation failure
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
        })->toArray();

        // Products as flat JSON for Alpine search (id, name, use_batches, barcodes[])
        $productsJson = $products->map(fn ($p) => [
            'id'         => $p->id,
            'name'       => $p->name,
            'use_batches'=> (bool) $p->use_batches,
            'barcodes'   => $p->barcodes->pluck('barcode')->toArray(),
        ])->values();
    @endphp

    {{-- Flash / validation errors --}}
    @if(session('error'))
        <div class="mb-4 rounded-lg bg-error-50 border border-error-200 px-4 py-3 text-sm text-error-700 dark:bg-error-500/10 dark:border-error-500/20 dark:text-error-400">
            {{ session('error') }}
        </div>
    @endif

    @php
        $lineErrorKeys = collect($errors->keys())->filter(fn ($k) => str_starts_with($k, 'lines.'));
    @endphp
    @if($lineErrorKeys->isNotEmpty())
        <div class="mb-4 rounded-lg bg-error-50 border border-error-200 px-4 py-3 text-sm text-error-700 dark:bg-error-500/10 dark:border-error-500/20 dark:text-error-400">
            <p class="mb-1 font-medium">Revise los errores en las líneas de producto:</p>
            <ul class="list-inside list-disc space-y-0.5">
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
              products: @json($productsJson),
              lines: @json(count($initialLines) ? $initialLines : []),
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
                  const q = this.lines[i].search.toLowerCase().trim();
                  if (q.length < 1) {
                      this.lines[i].results    = [];
                      this.lines[i].showResults = false;
                      return;
                  }
                  this.lines[i].results = this.products
                      .filter(p =>
                          p.name.toLowerCase().includes(q) ||
                          p.barcodes.some(b => b.includes(q))
                      )
                      .slice(0, 8);
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
        <div class="grid gap-5 md:grid-cols-2">
            <x-common.component-card title="Datos de la Compra">
                <div class="space-y-4">
                    {{-- Proveedor --}}
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                            Proveedor <span class="text-error-500">*</span>
                        </label>
                        <div x-data="{ isOptionSelected: {{ old('supplier_id') ? 'true' : 'false' }} }" class="relative z-20 bg-transparent">
                            <select name="supplier_id"
                                class="shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full appearance-none rounded-lg border bg-transparent px-4 py-2.5 pr-11 text-sm focus:ring-3 focus:outline-hidden dark:bg-gray-900 dark:text-white/90 {{ $errors->has('supplier_id') ? 'border-error-400 dark:border-error-500' : 'border-gray-300 dark:border-gray-700' }}"
                                :class="isOptionSelected ? 'text-gray-800 dark:text-white/90' : 'text-gray-400 dark:text-gray-500'"
                                @change="isOptionSelected = true">
                                <option value="">— Seleccionar proveedor —</option>
                                @foreach($suppliers as $supplier)
                                    <option value="{{ $supplier->id }}" {{ old('supplier_id') == $supplier->id ? 'selected' : '' }}
                                        class="text-gray-700 dark:bg-gray-900 dark:text-gray-400">{{ $supplier->name }}</option>
                                @endforeach
                            </select>
                            <span class="pointer-events-none absolute top-1/2 right-4 z-30 -translate-y-1/2 text-gray-500 dark:text-gray-400">
                                <svg class="stroke-current" width="20" height="20" viewBox="0 0 20 20" fill="none"><path d="M4.79175 7.396L10.0001 12.6043L15.2084 7.396" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            </span>
                        </div>
                        @error('supplier_id')<p class="mt-1 text-xs text-error-500">{{ $message }}</p>@enderror
                    </div>

                    {{-- Ubicación destino --}}
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                            Ubicación de destino <span class="text-error-500">*</span>
                        </label>
                        <div x-data="{ isOptionSelected: {{ old('location_id') ? 'true' : 'false' }} }" class="relative z-20 bg-transparent">
                            <select name="location_id"
                                class="shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full appearance-none rounded-lg border bg-transparent px-4 py-2.5 pr-11 text-sm focus:ring-3 focus:outline-hidden dark:bg-gray-900 dark:text-white/90 {{ $errors->has('location_id') ? 'border-error-400 dark:border-error-500' : 'border-gray-300 dark:border-gray-700' }}"
                                :class="isOptionSelected ? 'text-gray-800 dark:text-white/90' : 'text-gray-400 dark:text-gray-500'"
                                @change="isOptionSelected = true">
                                <option value="">— Seleccionar ubicación —</option>
                                @foreach($locations as $location)
                                    <option value="{{ $location->id }}" {{ old('location_id') == $location->id ? 'selected' : '' }}
                                        class="text-gray-700 dark:bg-gray-900 dark:text-gray-400">
                                        {{ $location->name }}
                                        ({{ $location->type->value === 'warehouse' ? 'Almacén' : 'Tienda' }})
                                    </option>
                                @endforeach
                            </select>
                            <span class="pointer-events-none absolute top-1/2 right-4 z-30 -translate-y-1/2 text-gray-500 dark:text-gray-400">
                                <svg class="stroke-current" width="20" height="20" viewBox="0 0 20 20" fill="none"><path d="M4.79175 7.396L10.0001 12.6043L15.2084 7.396" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            </span>
                        </div>
                        @error('location_id')<p class="mt-1 text-xs text-error-500">{{ $message }}</p>@enderror
                    </div>
                </div>
            </x-common.component-card>

            <x-common.component-card title="Información Adicional">
                <div class="space-y-4">
                    {{-- Referencia del documento --}}
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                            Nro. de Factura / Referencia
                        </label>
                        <input type="text" name="reference_doc" value="{{ old('reference_doc') }}"
                            placeholder="Ej: FAC-001234"
                            class="shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30" />
                    </div>

                    {{-- Notas --}}
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Notas</label>
                        <textarea name="notes" rows="3" placeholder="Observaciones opcionales..."
                            class="shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30">{{ old('notes') }}</textarea>
                    </div>
                </div>
            </x-common.component-card>
        </div>

        {{-- ── Líneas de productos ── --}}
        <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4 dark:border-gray-800">
                <h3 class="text-base font-medium text-gray-800 dark:text-white/90">Productos</h3>
                <button type="button" @click="addLine()"
                    class="rounded-lg bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200 dark:bg-white/10 dark:text-white/80 dark:hover:bg-white/20">
                    + Agregar producto
                </button>
            </div>

            <div class="divide-y divide-gray-100 dark:divide-gray-800">
                <template x-for="(line, i) in lines" :key="i">
                    <div class="relative px-6 py-5">
                        {{-- Remove button --}}
                        <button type="button" @click="removeLine(i)"
                            class="absolute top-4 right-4 flex h-7 w-7 items-center justify-center rounded-full bg-error-50 text-xs text-error-500 hover:bg-error-100 dark:bg-error-500/10 dark:hover:bg-error-500/20"
                            :class="lines.length === 1 ? 'opacity-30 cursor-not-allowed' : ''"
                            :disabled="lines.length === 1">✕</button>

                        <div class="grid gap-4 pr-10">
                            {{-- Product search --}}
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                    Producto <span class="text-error-500">*</span>
                                </label>
                                <div class="relative" @click.outside="line.showResults = false">
                                    <input type="text"
                                        x-model="line.search"
                                        @input="searchProduct(i)"
                                        @focus="if(line.search.length > 0) searchProduct(i)"
                                        placeholder="Buscar por nombre o código de barras..."
                                        autocomplete="off"
                                        class="shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30" />

                                    {{-- Hidden product_id for form submission --}}
                                    <input type="hidden" :name="`lines[${i}][product_id]`" :value="line.product_id" />

                                    {{-- Search dropdown --}}
                                    <div x-show="line.showResults"
                                         class="absolute top-full left-0 z-50 mt-1 max-h-60 w-full overflow-y-auto rounded-xl border border-gray-200 bg-white shadow-lg dark:border-gray-700 dark:bg-gray-800">
                                        <template x-for="result in line.results" :key="result.id">
                                            <button type="button"
                                                @click="selectProduct(i, result)"
                                                class="flex w-full items-center gap-3 px-4 py-2.5 text-left text-sm text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-white/[0.05]">
                                                <span class="flex-1" x-text="result.name"></span>
                                                <span x-show="result.use_batches"
                                                    class="rounded-full bg-brand-50 px-2 py-0.5 text-xs font-medium text-brand-600 dark:bg-brand-500/10 dark:text-brand-400">
                                                    PEPS
                                                </span>
                                            </button>
                                        </template>
                                    </div>
                                </div>
                                {{-- Show selected product name as confirmation --}}
                                <p x-show="line.product_id && !line.showResults"
                                   class="mt-1 text-xs text-success-600 dark:text-success-400">
                                    ✓ <span x-text="line.product_name"></span>
                                </p>
                            </div>

                            {{-- Batch fields (only when use_batches=true) --}}
                            <div x-show="line.use_batches" class="grid gap-4 sm:grid-cols-2">
                                <div>
                                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                        Código de Lote <span class="text-error-500">*</span>
                                    </label>
                                    <input type="text"
                                        :name="`lines[${i}][batch_code]`"
                                        x-model="line.batch_code"
                                        placeholder="Ej: LOT-2024-001"
                                        class="shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30" />
                                </div>
                                <div>
                                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                        Fecha de Vencimiento <span class="text-error-500">*</span>
                                    </label>
                                    <input type="date"
                                        :name="`lines[${i}][expiration_date]`"
                                        x-model="line.expiration_date"
                                        class="shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
                                </div>
                            </div>

                            {{-- Hidden batch fields when use_batches=false (submit empty so keys exist) --}}
                            <template x-if="!line.use_batches">
                                <div>
                                    <input type="hidden" :name="`lines[${i}][batch_code]`" value="" />
                                    <input type="hidden" :name="`lines[${i}][expiration_date]`" value="" />
                                </div>
                            </template>

                            {{-- Quantity, Cost, Subtotal --}}
                            <div class="grid gap-4 sm:grid-cols-3">
                                <div>
                                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                        Cantidad <span class="text-error-500">*</span>
                                    </label>
                                    <input type="number" step="0.01" min="0.01"
                                        :name="`lines[${i}][quantity]`"
                                        x-model.number="line.quantity"
                                        placeholder="0.00"
                                        class="shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
                                </div>
                                <div>
                                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                        Costo Unitario (Bs.) <span class="text-error-500">*</span>
                                    </label>
                                    <input type="number" step="0.01" min="0"
                                        :name="`lines[${i}][unit_cost]`"
                                        x-model.number="line.unit_cost"
                                        placeholder="0.00"
                                        class="shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
                                </div>
                                <div>
                                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Subtotal</label>
                                    <div class="flex h-11 items-center rounded-lg border border-gray-200 bg-gray-50 px-4 text-sm font-medium text-gray-800 dark:border-gray-700 dark:bg-white/[0.03] dark:text-white/90">
                                        Bs. <span class="ml-1" x-text="lineSubtotal(line)">0.00</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            {{-- Total footer --}}
            <div class="flex items-center justify-end gap-4 border-t border-gray-100 px-6 py-4 dark:border-gray-800">
                <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Total de la compra:</span>
                <span class="text-xl font-semibold text-gray-900 dark:text-white">
                    Bs. <span x-text="total">0.00</span>
                </span>
            </div>
        </div>

        {{-- ── Actions ── --}}
        <div class="flex items-center gap-3">
            <button type="submit"
                class="rounded-lg bg-brand-500 px-6 py-2.5 text-sm font-medium text-white hover:bg-brand-600">
                Registrar Compra
            </button>
            <a href="{{ route('inventory.purchases.index') }}"
               class="rounded-lg bg-gray-100 px-6 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-200 dark:bg-white/10 dark:text-white/80 dark:hover:bg-white/20">
                Cancelar
            </a>
        </div>
    </form>
@endsection
