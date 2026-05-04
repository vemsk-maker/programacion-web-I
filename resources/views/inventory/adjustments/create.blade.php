@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Nuevo Ajuste de Inventario" />

    @php
        $productsJson = $products->map(fn ($p) => [
            'id'   => $p->id,
            'name' => $p->name,
        ])->values()->toArray();
    @endphp

    <script>
        window.__adjustData = {
            products: @json($productsJson),
        };
    </script>

    {{-- Flash / errors --}}
    @if(session('error'))
        <div class="mb-6 flex items-center gap-3 rounded-2xl bg-red-50 border border-red-100 px-5 py-3 text-sm font-bold text-[#e11d48]">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6L6 18M6 6l12 12"/></svg>
            {{ session('error') }}
        </div>
    @endif

    @php $lineErrors = collect($errors->keys())->filter(fn ($k) => str_starts_with($k, 'lines.')); @endphp
    @if($lineErrors->isNotEmpty())
        <div class="mb-6 rounded-2xl bg-red-50 border border-red-100 px-5 py-4 text-sm text-[#e11d48]">
            <p class="mb-2 font-bold">Revise los errores en las líneas:</p>
            <ul class="list-inside list-disc space-y-0.5 font-medium">
                @foreach($lineErrors as $key)
                    <li>{{ $errors->first($key) }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div x-data="adjustmentForm()" x-init="init()">
        <form method="POST" action="{{ route('inventory.adjustments.store') }}" @submit.prevent="submitForm($el)">
            @csrf

            <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

                {{-- ── Panel izquierdo: Encabezado ─────────────────────────────── --}}
                <div class="xl:col-span-1 space-y-5">
                    <div class="rounded-3xl border border-gray-100 bg-white shadow-sm p-6 space-y-5">
                        <h3 class="text-base font-black text-[#1e293b] uppercase tracking-tight">Datos del Ajuste</h3>

                        {{-- Ubicación --}}
                        <div>
                            <label class="mb-1.5 block text-xs font-black text-gray-500 uppercase">
                                Ubicación <span class="text-[#e11d48]">*</span>
                            </label>
                            <div x-data="{ isOptionSelected: false }" class="relative">
                                <select name="location_id" required
                                    class="h-11 w-full appearance-none rounded-xl border border-gray-200 bg-gray-50 px-4 pr-10 text-sm font-semibold text-gray-400 focus:border-[#e11d48] focus:outline-none transition-all"
                                    :class="isOptionSelected ? 'text-[#1e293b]' : ''"
                                    @change="isOptionSelected = !!$event.target.value">
                                    <option value="">— Seleccione ubicación —</option>
                                    @foreach ($locations as $loc)
                                        <option value="{{ $loc->id }}" {{ old('location_id') == $loc->id ? 'selected' : '' }}>
                                            {{ $loc->name }} ({{ $loc->type->value }})
                                        </option>
                                    @endforeach
                                </select>
                                <span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-gray-400">
                                    <svg width="16" height="16" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M4.79175 7.396L10.0001 12.6043L15.2084 7.396" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                </span>
                            </div>
                            @error('location_id')
                                <p class="mt-1 text-xs font-black text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Notas --}}
                        <div>
                            <label class="mb-1.5 block text-xs font-black text-gray-500 uppercase">Motivo / Notas</label>
                            <textarea name="notes" rows="3"
                                placeholder="Ej: Conteo físico, merma detectada, corrección de error..."
                                class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm font-semibold text-[#1e293b] focus:border-[#e11d48] focus:outline-none transition-all resize-none">{{ old('notes') }}</textarea>
                        </div>

                        {{-- Botón submit --}}
                        <button type="submit"
                            class="w-full rounded-xl bg-[#e11d48] py-3 text-sm font-black uppercase tracking-widest text-white shadow-md hover:bg-[#be123c] transition-all active:scale-95">
                            Registrar Ajuste
                        </button>
                        <a href="{{ route('inventory.adjustments.index') }}"
                           class="block w-full rounded-xl bg-gray-100 py-2.5 text-center text-sm font-black uppercase text-gray-600 hover:bg-gray-200 transition-all">
                            Cancelar
                        </a>
                    </div>
                </div>

                {{-- ── Panel derecho: Líneas ────────────────────────────────────── --}}
                <div class="xl:col-span-2">
                    <div class="rounded-3xl border border-gray-100 bg-white shadow-sm overflow-hidden">
                        <div class="flex items-center justify-between border-b border-gray-50 px-6 py-4">
                            <h3 class="text-base font-black text-[#1e293b] uppercase tracking-tight">Líneas de Producto</h3>
                            <button type="button" @click="addLine()"
                                class="flex h-9 items-center gap-2 rounded-xl bg-[#e11d48] px-4 text-xs font-black uppercase tracking-widest text-white hover:bg-[#be123c] transition-all active:scale-95">
                                + Agregar línea
                            </button>
                        </div>

                        {{-- Cabecera de tabla --}}
                        <div class="hidden md:grid grid-cols-12 gap-3 px-6 py-3 bg-gray-50/60 border-b border-gray-50 text-[11px] font-black uppercase tracking-wider text-gray-400">
                            <div class="col-span-5">Producto</div>
                            <div class="col-span-3 text-center">Cantidad <span class="text-gray-300 font-medium normal-case">(+ añadir / - quitar)</span></div>
                            <div class="col-span-3 text-center">Costo unit. (Bs.)</div>
                            <div class="col-span-1"></div>
                        </div>

                        {{-- Líneas dinámicas --}}
                        <div class="divide-y divide-gray-50" id="lines-container">
                            <template x-for="(line, index) in lines" :key="line.key">
                                <div class="grid grid-cols-12 gap-3 items-center px-6 py-3">

                                    {{-- Producto --}}
                                    <div class="col-span-5">
                                        <input type="hidden" :name="`lines[${index}][product_id]`" :value="line.product_id" />
                                        <input type="text"
                                            x-model="line.productSearch"
                                            @input="searchProducts(index, $event.target.value)"
                                            @focus="line.showSuggestions = true"
                                            @blur.delay.300ms="line.showSuggestions = false"
                                            placeholder="Buscar producto..."
                                            class="h-10 w-full rounded-xl border border-gray-200 bg-gray-50 px-3 text-sm font-semibold text-[#1e293b] focus:border-[#e11d48] focus:outline-none transition-all"
                                            autocomplete="off" />
                                        {{-- Sugerencias --}}
                                        <div x-show="line.showSuggestions && line.suggestions.length"
                                             class="relative z-10">
                                            <ul class="absolute w-full mt-1 max-h-40 overflow-y-auto rounded-xl border border-gray-100 bg-white shadow-xl">
                                                <template x-for="sug in line.suggestions" :key="sug.id">
                                                    <li @mousedown.prevent="selectProduct(index, sug)"
                                                        class="cursor-pointer px-4 py-2 text-sm font-semibold text-[#1e293b] hover:bg-red-50 hover:text-[#e11d48] transition-colors"
                                                        x-text="sug.name">
                                                    </li>
                                                </template>
                                            </ul>
                                        </div>
                                    </div>

                                    {{-- Cantidad --}}
                                    <div class="col-span-3">
                                        <input type="number" :name="`lines[${index}][quantity]`"
                                            x-model.number="line.quantity"
                                            step="0.001"
                                            placeholder="0"
                                            class="h-10 w-full rounded-xl border text-center font-black text-sm focus:outline-none transition-all"
                                            :class="line.quantity > 0 ? 'border-green-300 bg-green-50 text-green-700 focus:border-green-500' : (line.quantity < 0 ? 'border-red-300 bg-red-50 text-red-700 focus:border-red-500' : 'border-gray-200 bg-gray-50 text-gray-700 focus:border-[#e11d48]')" />
                                    </div>

                                    {{-- Costo --}}
                                    <div class="col-span-3">
                                        <input type="number" :name="`lines[${index}][unit_cost]`"
                                            x-model.number="line.unit_cost"
                                            step="0.01" min="0"
                                            placeholder="Opcional"
                                            class="h-10 w-full rounded-xl border border-gray-200 bg-gray-50 px-3 text-sm font-semibold text-[#1e293b] focus:border-[#e11d48] focus:outline-none transition-all" />
                                    </div>

                                    {{-- Eliminar --}}
                                    <div class="col-span-1 text-center">
                                        <button type="button" @click="removeLine(index)"
                                            class="rounded-lg p-2 text-gray-300 hover:bg-red-50 hover:text-[#e11d48] transition-colors">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6L6 18M6 6l12 12"/></svg>
                                        </button>
                                    </div>
                                </div>
                            </template>

                            {{-- Estado vacío --}}
                            <div x-show="lines.length === 0" class="px-6 py-12 text-center">
                                <p class="text-sm font-bold text-gray-400">No hay líneas. Haga clic en "Agregar línea".</p>
                            </div>
                        </div>

                        {{-- Footer resumen --}}
                        <div x-show="lines.length > 0" class="border-t border-gray-50 bg-gray-50/40 px-6 py-3 flex items-center gap-6 text-sm">
                            <span class="font-bold text-gray-400 uppercase text-xs tracking-wider">
                                <span x-text="lines.length"></span> línea(s)
                            </span>
                            <span class="font-bold text-green-600">
                                + <span x-text="lines.filter(l=>l.quantity>0).reduce((s,l)=>s+Math.abs(l.quantity),0).toFixed(2)"></span> uds. a ingresar
                            </span>
                            <span class="font-bold text-red-600">
                                - <span x-text="lines.filter(l=>l.quantity<0).reduce((s,l)=>s+Math.abs(l.quantity),0).toFixed(2)"></span> uds. a retirar
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
<script>
    function adjustmentForm() {
        return {
            lines: [],
            products: window.__adjustData.products || [],
            _key: 0,

            init() {
                this.addLine();
            },

            addLine() {
                this.lines.push({
                    key:             this._key++,
                    product_id:      '',
                    productSearch:   '',
                    suggestions:     [],
                    showSuggestions: false,
                    quantity:        '',
                    unit_cost:       '',
                });
            },

            removeLine(index) {
                this.lines.splice(index, 1);
            },

            searchProducts(index, query) {
                if (!query || query.length < 2) {
                    this.lines[index].suggestions = [];
                    return;
                }
                const q = query.toLowerCase();
                this.lines[index].suggestions = this.products
                    .filter(p => p.name.toLowerCase().includes(q))
                    .slice(0, 8);
            },

            selectProduct(index, product) {
                this.lines[index].product_id    = product.id;
                this.lines[index].productSearch = product.name;
                this.lines[index].suggestions   = [];
                this.lines[index].showSuggestions = false;
            },

            submitForm(form) {
                // Validate at least one line with product and non-zero quantity
                const valid = this.lines.every(l => l.product_id && l.quantity !== '' && l.quantity !== 0);
                if (!valid || this.lines.length === 0) {
                    alert('Agregue al menos una línea con producto y cantidad distinta de cero.');
                    return;
                }
                form.submit();
            },
        };
    }
</script>
@endpush
