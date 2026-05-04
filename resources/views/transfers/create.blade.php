@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Nuevo Traslado" />

    @php
        $productsJson = $products->map(fn ($p) => [
            'id'          => $p->id,
            'name'        => $p->name,
            'use_batches' => (bool) $p->use_batches,
            'barcodes'    => $p->barcodes->pluck('barcode')->toArray(),
        ])->values()->toArray();

        $stockEndpoint = route('inventory.transfers.stock');
    @endphp

    {{-- ═══ Datos para Alpine fuera del atributo HTML para evitar conflictos de comillas ═══ --}}
    <script>
        window.__transferData = {
            products:       @json($productsJson),
            stockEndpoint:  @json($stockEndpoint),
            fromLocationId: @json(old('from_location_id', '')),
            toLocationId:   @json(old('to_location_id', '')),
        };
    </script>

    {{-- Flash / errors --}}
    @if(session('error'))
        <div class="mb-6 flex items-center gap-3 rounded-2xl bg-red-50 border border-red-100 px-5 py-3 text-sm font-bold text-[#e11d48]">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6L6 18M6 6l12 12"/></svg>
            {{ session('error') }}
        </div>
    @endif

    @php $lineErrorKeys = collect($errors->keys())->filter(fn ($k) => str_starts_with($k, 'lines.')); @endphp
    @if($lineErrorKeys->isNotEmpty())
        <div class="mb-6 rounded-2xl bg-red-50 border border-red-100 px-5 py-4 text-sm text-[#e11d48]">
            <p class="mb-2 font-bold">Revise los errores en las líneas de producto:</p>
            <ul class="list-inside list-disc space-y-0.5 font-medium">
                @foreach($lineErrorKeys as $key)
                    @foreach($errors->get($key) as $msg)<li>{{ $msg }}</li>@endforeach
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('inventory.transfers.store') }}"
          x-data="{
              products:       window.__transferData.products,
              stockEndpoint:  window.__transferData.stockEndpoint,
              fromLocationId: window.__transferData.fromLocationId,
              toLocationId:   window.__transferData.toLocationId,
              lines: [],
              init() { this.addLine(); },

              newLine() {
                  return {
                      product_id:   '',
                      product_name: '',
                      use_batches:  false,
                      search:       '',
                      results:      [],
                      showResults:  false,
                      batch_id:     '',
                      batches:      [],
                      available:    null,
                      loading:      false,
                      quantity:     '',
                  };
              },
              addLine()     { this.lines.push(this.newLine()); },
              removeLine(i) { if (this.lines.length > 1) this.lines.splice(i, 1); },

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
                      const r    = await fetch(`${this.stockEndpoint}?${params}`, {
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

              onOriginChange() {
                  this.lines.forEach((line, i) => {
                      line.batch_id  = '';
                      line.batches   = [];
                      line.available = null;
                      line.quantity  = '';
                      if (line.product_id) this.fetchStock(i);
                  });
              },

              clampQuantity(i) {
                  const line = this.lines[i];
                  if (line.available !== null && parseFloat(line.quantity) > line.available) {
                      line.quantity = line.available;
                  }
              },
          }"
          class="space-y-6">
        @csrf

        {{-- ── Cabecera ── --}}
        <div class="rounded-[2.5rem] border border-gray-100 bg-white p-8 shadow-sm">
            <div class="mb-6 border-b border-gray-50 pb-6">
                <h3 class="text-2xl font-bold text-[#1e293b]">Nuevo Traslado</h3>
                <p class="text-sm text-gray-500">Mueva mercadería entre ubicaciones del inventario</p>
            </div>

            <div class="grid gap-6 md:grid-cols-2">
                {{-- Ubicaciones --}}
                <div class="space-y-5">
                    <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400">Ubicaciones</p>

                    {{-- Origen --}}
                    <div>
                        <label class="mb-1.5 block text-sm font-bold text-[#1e293b]">
                            Origen <span class="text-[#e11d48]">*</span>
                        </label>
                        <div class="relative">
                            <select name="from_location_id"
                                x-model="fromLocationId"
                                @change="onOriginChange()"
                                class="h-11 w-full appearance-none rounded-xl border border-gray-200 bg-white px-4 pr-10 text-sm text-gray-700 focus:border-gray-400 focus:outline-none transition-all {{ $errors->has('from_location_id') ? 'border-[#e11d48]' : '' }}">
                                <option value="">— Seleccionar origen —</option>
                                @foreach($locations as $loc)
                                    <option value="{{ $loc->id }}" {{ old('from_location_id') == $loc->id ? 'selected' : '' }}>
                                        {{ $loc->name }}
                                    </option>
                                @endforeach
                            </select>
                            <span class="pointer-events-none absolute top-1/2 right-4 -translate-y-1/2 text-gray-400">
                                <svg width="16" height="16" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M4.79175 7.396L10.0001 12.6043L15.2084 7.396" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            </span>
                        </div>
                        @error('from_location_id')
                            <p class="mt-1 text-xs font-bold text-[#e11d48]">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Destino --}}
                    <div>
                        <label class="mb-1.5 block text-sm font-bold text-[#1e293b]">
                            Destino <span class="text-[#e11d48]">*</span>
                        </label>
                        <div class="relative">
                            <select name="to_location_id"
                                x-model="toLocationId"
                                class="h-11 w-full appearance-none rounded-xl border border-gray-200 bg-white px-4 pr-10 text-sm text-gray-700 focus:border-gray-400 focus:outline-none transition-all {{ $errors->has('to_location_id') ? 'border-[#e11d48]' : '' }}">
                                <option value="">— Seleccionar destino —</option>
                                @foreach($locations as $loc)
                                    <option value="{{ $loc->id }}" {{ old('to_location_id') == $loc->id ? 'selected' : '' }}>
                                        {{ $loc->name }}
                                    </option>
                                @endforeach
                            </select>
                            <span class="pointer-events-none absolute top-1/2 right-4 -translate-y-1/2 text-gray-400">
                                <svg width="16" height="16" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M4.79175 7.396L10.0001 12.6043L15.2084 7.396" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            </span>
                        </div>
                        @error('to_location_id')
                            <p class="mt-1 text-xs font-bold text-[#e11d48]">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Información adicional --}}
                <div class="space-y-5">
                    <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400">Información Adicional</p>

                    <div>
                        <label class="mb-1.5 block text-sm font-bold text-[#1e293b]">Notas</label>
                        <textarea name="notes" rows="4" placeholder="Ej: solicitud de tienda, reposición mensual..."
                            class="w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-700 placeholder:text-gray-400 focus:border-gray-400 focus:outline-none transition-all">{{ old('notes') }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Líneas de productos ── --}}
        <div class="rounded-[2.5rem] border border-gray-100 bg-white shadow-sm">

            <div class="flex items-center justify-between border-b border-gray-50 px-8 py-6">
                <div>
                    <h3 class="text-xl font-bold text-[#1e293b]">Productos a trasladar</h3>
                    <p class="text-sm text-gray-500">Agregue los ítems que desea mover</p>
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

                                    {{-- Dropdown resultados --}}
                                    <div x-show="line.showResults"
                                         class="absolute top-full left-0 z-50 mt-1 max-h-60 w-full overflow-y-auto rounded-xl border border-gray-100 bg-white shadow-lg">
                                        <template x-for="result in line.results" :key="result.id">
                                            <button type="button" @click="selectProduct(i, result)"
                                                class="flex w-full items-center gap-3 px-4 py-2.5 text-left text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                                                <span class="flex-1 font-semibold" x-text="result.name"></span>
                                                <span x-show="result.use_batches"
                                                    class="inline-flex items-center rounded-lg bg-gray-100 px-2.5 py-0.5 text-[10px] font-bold uppercase text-gray-500">
                                                    PEPS
                                                </span>
                                            </button>
                                        </template>
                                    </div>
                                </div>

                                {{-- Confirmación + badge stock --}}
                                <div x-show="line.product_id" class="mt-1.5 flex flex-wrap items-center gap-2">
                                    <span class="text-xs font-bold text-emerald-600">
                                        ✓ <span x-text="line.product_name"></span>
                                    </span>
                                    <template x-if="line.loading">
                                        <span class="text-xs font-medium text-gray-400">Cargando stock...</span>
                                    </template>
                                    <template x-if="!line.loading && line.available !== null">
                                        <span class="inline-flex items-center rounded-lg px-2.5 py-0.5 text-xs font-bold"
                                            :class="line.available > 0
                                                ? 'bg-emerald-50 text-emerald-600'
                                                : 'bg-red-50 text-[#e11d48]'">
                                            Disponible: <span class="ml-1" x-text="line.available"></span>
                                        </span>
                                    </template>
                                    <template x-if="!line.loading && line.available === null && line.product_id && !fromLocationId">
                                        <span class="text-xs font-bold text-amber-500">Seleccione origen para ver stock</span>
                                    </template>
                                </div>
                            </div>

                            {{-- Selector de lote (solo si use_batches=true) --}}
                            <template x-if="line.use_batches">
                                <div>
                                    <label class="mb-1.5 block text-sm font-bold text-[#1e293b]">Lote</label>
                                    <div class="relative">
                                        <select :name="`lines[${i}][batch_id]`"
                                            x-model="line.batch_id"
                                            @change="fetchStock(i)"
                                            class="h-11 w-full appearance-none rounded-xl border border-gray-200 bg-white px-4 pr-10 text-sm text-gray-700 focus:border-gray-400 focus:outline-none transition-all">
                                            <option value="">— Sin lote específico (PEPS automático) —</option>
                                            <template x-for="batch in line.batches" :key="batch.id">
                                                <option :value="batch.id"
                                                    x-text="`${batch.batch_code}${batch.expiration_date ? ' — vence: ' + batch.expiration_date : ''} (disp: ${batch.available})`">
                                                </option>
                                            </template>
                                        </select>
                                        <span class="pointer-events-none absolute top-1/2 right-4 -translate-y-1/2 text-gray-400">
                                            <svg width="16" height="16" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M4.79175 7.396L10.0001 12.6043L15.2084 7.396" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                        </span>
                                    </div>
                                </div>
                            </template>

                            {{-- Hidden batch_id cuando no usa lotes --}}
                            <template x-if="!line.use_batches">
                                <input type="hidden" :name="`lines[${i}][batch_id]`" value="" />
                            </template>

                            {{-- Cantidad --}}
                            <div class="max-w-xs">
                                <label class="mb-1.5 block text-sm font-bold text-[#1e293b]">
                                    Cantidad <span class="text-[#e11d48]">*</span>
                                </label>
                                <input type="number" step="0.01" min="0.01"
                                    :name="`lines[${i}][quantity]`"
                                    x-model.number="line.quantity"
                                    @change="clampQuantity(i)"
                                    :max="line.available ?? undefined"
                                    placeholder="0.00"
                                    class="h-11 w-full rounded-xl border border-gray-200 bg-white px-4 text-sm text-gray-700 focus:border-gray-400 focus:outline-none transition-all" />
                                <template x-if="line.available !== null && parseFloat(line.quantity) > line.available">
                                    <p class="mt-1 text-xs font-bold text-[#e11d48]">No puede superar el stock disponible.</p>
                                </template>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            {{-- Footer --}}
            <div class="rounded-b-[2.5rem] border-t border-gray-50 bg-gray-50/30 px-8 py-4">
                <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400"
                   x-text="`${lines.length} producto${lines.length !== 1 ? 's' : ''} en este traslado`"></p>
            </div>
        </div>

        {{-- ── Acciones ── --}}
        <div class="flex items-center gap-3">
            <button type="submit"
                class="flex h-11 items-center gap-2 rounded-xl bg-[#e11d48] px-8 text-sm font-bold text-white shadow-md transition-all hover:bg-[#be123c] active:scale-95">
                Registrar Traslado
            </button>
            <a href="{{ route('inventory.transfers.index') }}"
               class="flex h-11 items-center rounded-xl bg-gray-100 px-8 text-sm font-bold text-gray-500 hover:bg-gray-200 hover:text-[#1e293b] transition-all">
                Cancelar
            </a>
        </div>
    </form>
@endsection