@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Nuevo Traslado" />

    @php
        $productsJson = $products->map(fn ($p) => [
            'id'          => $p->id,
            'name'        => $p->name,
            'use_batches' => (bool) $p->use_batches,
            'barcodes'    => $p->barcodes->pluck('barcode')->toArray(),
        ])->values();

        $stockEndpoint = route('inventory.transfers.stock');
    @endphp

    {{-- Flash / errors --}}
    @if(session('error'))
        <div class="mb-4 rounded-lg bg-error-50 border border-error-200 px-4 py-3 text-sm text-error-700 dark:bg-error-500/10 dark:border-error-500/20 dark:text-error-400">
            {{ session('error') }}
        </div>
    @endif

    @php $lineErrorKeys = collect($errors->keys())->filter(fn ($k) => str_starts_with($k, 'lines.')); @endphp
    @if($lineErrorKeys->isNotEmpty())
        <div class="mb-4 rounded-lg bg-error-50 border border-error-200 px-4 py-3 text-sm text-error-700 dark:bg-error-500/10 dark:border-error-500/20 dark:text-error-400">
            <p class="mb-1 font-medium">Revise los errores en las líneas de producto:</p>
            <ul class="list-inside list-disc space-y-0.5">
                @foreach($lineErrorKeys as $key)
                    @foreach($errors->get($key) as $msg)<li>{{ $msg }}</li>@endforeach
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('inventory.transfers.store') }}"
          x-data="{
              products: @json($productsJson),
              stockEndpoint: '{{ $stockEndpoint }}',
              fromLocationId: '{{ old('from_location_id', '') }}',
              toLocationId:   '{{ old('to_location_id', '') }}',
              lines: [],
              init() { this.addLine(); },

              // ── helpers ──────────────────────────────────────────────────────
              newLine() {
                  return {
                      product_id:   '',
                      product_name: '',
                      use_batches:  false,
                      search:       '',
                      results:      [],
                      showResults:  false,
                      batch_id:     '',
                      batches:      [],   // available batches from AJAX
                      available:    null, // stock available (number or null)
                      loading:      false,
                      quantity:     '',
                  };
              },
              addLine()     { this.lines.push(this.newLine()); },
              removeLine(i) { if (this.lines.length > 1) this.lines.splice(i, 1); },

              // ── product search ───────────────────────────────────────────────
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
                      ).slice(0, 8);
                  this.lines[i].showResults = this.lines[i].results.length > 0;
              },
              selectProduct(i, p) {
                  const line        = this.lines[i];
                  line.product_id   = p.id;
                  line.product_name = p.name;
                  line.use_batches  = p.use_batches;
                  line.search       = p.name;
                  line.showResults  = false;
                  line.results      = [];
                  line.batch_id     = '';
                  line.batches      = [];
                  line.available    = null;
                  line.quantity     = '';
                  if (this.fromLocationId) this.fetchStock(i);
              },

              // ── AJAX stock ───────────────────────────────────────────────────
              async fetchStock(i) {
                  const line = this.lines[i];
                  if (!line.product_id || !this.fromLocationId) { line.available = null; return; }
                  line.loading = true;
                  const params = new URLSearchParams({
                      product_id:  line.product_id,
                      location_id: this.fromLocationId,
                      ...(line.batch_id ? { batch_id: line.batch_id } : {}),
                  });
                  try {
                      const r = await fetch(`${this.stockEndpoint}?${params}`, {
                          headers: { 'X-Requested-With': 'XMLHttpRequest' }
                      });
                      const data = await r.json();
                      line.available = data.available;
                      line.batches   = data.batches ?? [];
                  } catch(e) {
                      line.available = null;
                  } finally {
                      line.loading = false;
                  }
              },

              // When origin changes, reset all lines stock info
              onOriginChange() {
                  this.lines.forEach((line, i) => {
                      line.batch_id  = '';
                      line.batches   = [];
                      line.available = null;
                      line.quantity  = '';
                      if (line.product_id) this.fetchStock(i);
                  });
              },

              // Quantity guard
              clampQuantity(i) {
                  const line = this.lines[i];
                  if (line.available !== null && parseFloat(line.quantity) > line.available) {
                      line.quantity = line.available;
                  }
              },
          }"
          class="space-y-6">
        @csrf

        {{-- ── Header: origin + destination + notes ── --}}
        <div class="grid gap-5 md:grid-cols-2">
            <x-common.component-card title="Ubicaciones">
                <div class="space-y-4">
                    {{-- Origen --}}
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                            Origen <span class="text-error-500">*</span>
                        </label>
                        <div x-data="{ isOptionSelected: {{ old('from_location_id') ? 'true' : 'false' }} }" class="relative z-20 bg-transparent">
                            <select name="from_location_id"
                                x-model="fromLocationId"
                                @change="isOptionSelected = true; onOriginChange()"
                                class="shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full appearance-none rounded-lg border bg-transparent px-4 py-2.5 pr-11 text-sm focus:ring-3 focus:outline-hidden dark:bg-gray-900 dark:text-white/90 {{ $errors->has('from_location_id') ? 'border-error-400 dark:border-error-500' : 'border-gray-300 dark:border-gray-700' }}"
                                :class="isOptionSelected ? 'text-gray-800 dark:text-white/90' : 'text-gray-400 dark:text-gray-500'">
                                <option value="">— Seleccionar origen —</option>
                                @foreach($locations as $loc)
                                    <option value="{{ $loc->id }}" {{ old('from_location_id') == $loc->id ? 'selected' : '' }}
                                        class="text-gray-700 dark:bg-gray-900 dark:text-gray-400">{{ $loc->name }}</option>
                                @endforeach
                            </select>
                            <span class="pointer-events-none absolute top-1/2 right-4 z-30 -translate-y-1/2 text-gray-500 dark:text-gray-400">
                                <svg class="stroke-current" width="20" height="20" viewBox="0 0 20 20" fill="none"><path d="M4.79175 7.396L10.0001 12.6043L15.2084 7.396" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            </span>
                        </div>
                        @error('from_location_id')<p class="mt-1 text-xs text-error-500">{{ $message }}</p>@enderror
                    </div>

                    {{-- Destino --}}
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                            Destino <span class="text-error-500">*</span>
                        </label>
                        <div x-data="{ isOptionSelected: {{ old('to_location_id') ? 'true' : 'false' }} }" class="relative z-20 bg-transparent">
                            <select name="to_location_id"
                                x-model="toLocationId"
                                @change="isOptionSelected = true"
                                class="shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full appearance-none rounded-lg border bg-transparent px-4 py-2.5 pr-11 text-sm focus:ring-3 focus:outline-hidden dark:bg-gray-900 dark:text-white/90 {{ $errors->has('to_location_id') ? 'border-error-400 dark:border-error-500' : 'border-gray-300 dark:border-gray-700' }}"
                                :class="isOptionSelected ? 'text-gray-800 dark:text-white/90' : 'text-gray-400 dark:text-gray-500'">
                                <option value="">— Seleccionar destino —</option>
                                @foreach($locations as $loc)
                                    <option value="{{ $loc->id }}" {{ old('to_location_id') == $loc->id ? 'selected' : '' }}
                                        class="text-gray-700 dark:bg-gray-900 dark:text-gray-400">{{ $loc->name }}</option>
                                @endforeach
                            </select>
                            <span class="pointer-events-none absolute top-1/2 right-4 z-30 -translate-y-1/2 text-gray-500 dark:text-gray-400">
                                <svg class="stroke-current" width="20" height="20" viewBox="0 0 20 20" fill="none"><path d="M4.79175 7.396L10.0001 12.6043L15.2084 7.396" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            </span>
                        </div>
                        @error('to_location_id')<p class="mt-1 text-xs text-error-500">{{ $message }}</p>@enderror
                    </div>
                </div>
            </x-common.component-card>

            <x-common.component-card title="Información Adicional">
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Notas</label>
                    <textarea name="notes" rows="4" placeholder="Observaciones opcionales..."
                        class="shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30">{{ old('notes') }}</textarea>
                    <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">Ej: solicitud de tienda, reposición mensual...</p>
                </div>
            </x-common.component-card>
        </div>

        {{-- ── Lines ── --}}
        <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4 dark:border-gray-800">
                <h3 class="text-base font-medium text-gray-800 dark:text-white/90">Productos a trasladar</h3>
                <button type="button" @click="addLine()"
                    class="rounded-lg bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200 dark:bg-white/10 dark:text-white/80 dark:hover:bg-white/20">
                    + Agregar producto
                </button>
            </div>

            <div class="divide-y divide-gray-100 dark:divide-gray-800">
                <template x-for="(line, i) in lines" :key="i">
                    <div class="relative px-6 py-5">
                        {{-- Remove --}}
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
                                    <input type="hidden" :name="`lines[${i}][product_id]`" :value="line.product_id" />

                                    {{-- Dropdown --}}
                                    <div x-show="line.showResults"
                                         class="absolute top-full left-0 z-50 mt-1 max-h-60 w-full overflow-y-auto rounded-xl border border-gray-200 bg-white shadow-lg dark:border-gray-700 dark:bg-gray-800">
                                        <template x-for="result in line.results" :key="result.id">
                                            <button type="button" @click="selectProduct(i, result)"
                                                class="flex w-full items-center gap-3 px-4 py-2.5 text-left text-sm text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-white/[0.05]">
                                                <span class="flex-1" x-text="result.name"></span>
                                                <span x-show="result.use_batches"
                                                    class="rounded-full bg-brand-50 px-2 py-0.5 text-xs font-medium text-brand-600 dark:bg-brand-500/10 dark:text-brand-400">PEPS</span>
                                            </button>
                                        </template>
                                    </div>
                                </div>

                                {{-- Confirmed product + stock badge --}}
                                <div x-show="line.product_id" class="mt-1.5 flex items-center gap-2">
                                    <span class="text-xs text-success-600 dark:text-success-400">
                                        ✓ <span x-text="line.product_name"></span>
                                    </span>
                                    <template x-if="line.loading">
                                        <span class="text-xs text-gray-400 dark:text-gray-500">Cargando stock...</span>
                                    </template>
                                    <template x-if="!line.loading && line.available !== null">
                                        <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium"
                                            :class="line.available > 0
                                                ? 'bg-success-50 text-success-700 dark:bg-success-500/10 dark:text-success-400'
                                                : 'bg-error-50 text-error-600 dark:bg-error-500/10 dark:text-error-400'">
                                            Disponible: <span class="ml-1" x-text="line.available"></span>
                                        </span>
                                    </template>
                                    <template x-if="!line.loading && line.available === null && line.product_id && !fromLocationId">
                                        <span class="text-xs text-warning-600 dark:text-warning-400">Seleccione origen para ver stock</span>
                                    </template>
                                </div>
                            </div>

                            {{-- Batch selector (only when use_batches=true) --}}
                            <div x-show="line.use_batches">
                                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Lote</label>
                                <div class="relative z-20 bg-transparent">
                                    <select :name="`lines[${i}][batch_id]`"
                                        x-model="line.batch_id"
                                        @change="fetchStock(i)"
                                        class="shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 pr-11 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                                        <option value="">— Sin lote específico (PEPS automático) —</option>
                                        <template x-for="batch in line.batches" :key="batch.id">
                                            <option :value="batch.id" class="text-gray-700 dark:bg-gray-900 dark:text-gray-400">
                                                <span x-text="batch.batch_code"></span>
                                                <template x-if="batch.expiration_date">
                                                    <span x-text="` — vence: ${batch.expiration_date}`"></span>
                                                </template>
                                                <span x-text="` (disp: ${batch.available})`"></span>
                                            </option>
                                        </template>
                                    </select>
                                    <span class="pointer-events-none absolute top-1/2 right-4 z-30 -translate-y-1/2 text-gray-500 dark:text-gray-400">
                                        <svg class="stroke-current" width="20" height="20" viewBox="0 0 20 20" fill="none"><path d="M4.79175 7.396L10.0001 12.6043L15.2084 7.396" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                    </span>
                                </div>
                            </div>

                            {{-- Hidden batch_id when no batches --}}
                            <template x-if="!line.use_batches">
                                <input type="hidden" :name="`lines[${i}][batch_id]`" value="" />
                            </template>

                            {{-- Quantity --}}
                            <div class="max-w-xs">
                                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                    Cantidad <span class="text-error-500">*</span>
                                </label>
                                <input type="number" step="0.01" min="0.01"
                                    :name="`lines[${i}][quantity]`"
                                    x-model.number="line.quantity"
                                    @change="clampQuantity(i)"
                                    :max="line.available ?? undefined"
                                    placeholder="0.00"
                                    class="shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
                                <template x-if="line.available !== null && parseFloat(line.quantity) > line.available">
                                    <p class="mt-1 text-xs text-error-500">No puede superar el stock disponible.</p>
                                </template>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        {{-- Actions --}}
        <div class="flex items-center gap-3">
            <button type="submit"
                class="rounded-lg bg-brand-500 px-6 py-2.5 text-sm font-medium text-white hover:bg-brand-600">
                Registrar Traslado
            </button>
            <a href="{{ route('inventory.transfers.index') }}"
               class="rounded-lg bg-gray-100 px-6 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-200 dark:bg-white/10 dark:text-white/80 dark:hover:bg-white/20">
                Cancelar
            </a>
        </div>
    </form>
@endsection
