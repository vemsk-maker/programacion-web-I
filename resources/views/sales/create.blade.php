@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Punto de Venta" />

    <div
        x-data="{
            locationId:     '{{ $locations->count() === 1 ? $locations->first()->id : '' }}',
            searchQuery:    '',
            searchResults:  [],
            searchLoading:  false,
            searchTimer:    null,
            showResults:    false,
            categoryFilter: '',
            priceMin:       '',
            priceMax:       '',
            categories:     @json($categories),

            cart:        [],
            clientName:  '',
            clientNit:   '',

            submitting: false,
            lastDoc:    null,
            lastDocUrl: null,
            errors:     {},

            searchEndpoint: '{{ route('sales.search') }}',
            storeEndpoint:  '{{ route('sales.store') }}',
            csrfToken:      '',

            get cartTotal() {
                return this.cart.reduce((s, l) => s + (parseFloat(l.qty) || 0) * (parseFloat(l.price) || 0), 0);
            },
            get canSubmit() {
                return this.cart.length > 0 && !!this.locationId && !this.submitting;
            },

            init() {
                this.csrfToken = document.querySelector('meta[name=csrf-token]')?.content ?? '';
                this.$nextTick(() => this.$refs.searchInput?.focus());
            },

            onSearchInput() {
                clearTimeout(this.searchTimer);
                this.errors = {};
                const q = this.searchQuery.trim();
                const hasFilters = this.categoryFilter || this.priceMin || this.priceMax;
                if (q.length < 2 && !hasFilters) { this.searchResults = []; this.showResults = false; return; }
                this.searchTimer = setTimeout(() => this.doSearch(q), 300);
            },

            onFilterChange() {
                clearTimeout(this.searchTimer);
                const q = this.searchQuery.trim();
                const hasFilters = this.categoryFilter || this.priceMin || this.priceMax;
                if (!hasFilters && q.length < 2) { this.searchResults = []; this.showResults = false; return; }
                this.searchTimer = setTimeout(() => this.doSearch(q), 250);
            },

            async doSearch(q) {
                if (!this.locationId) return;
                this.searchLoading = true;
                this.showResults   = true;
                const params = new URLSearchParams({ q, location_id: this.locationId });
                if (this.categoryFilter) params.set('category_id', this.categoryFilter);
                if (this.priceMin)       params.set('price_min', this.priceMin);
                if (this.priceMax)       params.set('price_max', this.priceMax);
                try {
                    const r = await fetch(`${this.searchEndpoint}?${params}`, {
                        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                    });
                    this.searchResults = await r.json();
                } catch(e) {
                    this.searchResults = [];
                }
                this.searchLoading = false;
            },

            onSearchEnter() {
                if (this.searchResults.length > 0) this.addToCart(this.searchResults[0]);
            },

            addToCart(product) {
                const idx = this.cart.findIndex(l => l.product_id === product.id);
                if (idx !== -1) {
                    this.cart[idx].qty = parseFloat(this.cart[idx].qty) + 1;
                } else {
                    this.cart.push({
                        product_id:  product.id,
                        name:        product.name,
                        unit:        product.unit_of_measure || '',
                        use_batches: product.use_batches,
                        stock:       product.stock,
                        qty:         1,
                        price:       product.sale_price !== null && product.sale_price !== undefined ? product.sale_price : 0,
                    });
                }
                this.searchQuery   = '';
                this.searchResults = [];
                this.showResults   = false;
                this.errors        = {};
                this.$nextTick(() => this.$refs.searchInput?.focus());
            },

            removeFromCart(i) { this.cart.splice(i, 1); },

            clearCart() {
                this.cart       = [];
                this.clientName = '';
                this.clientNit  = '';
                this.errors     = {};
                this.$nextTick(() => this.$refs.searchInput?.focus());
            },

            async confirmSale() {
                if (!this.canSubmit) return;
                this.submitting = true;
                this.errors     = {};
                try {
                    const r = await fetch(this.storeEndpoint, {
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
                        this.$nextTick(() => this.$refs.searchInput?.focus());
                    } else if (r.status === 422 && data.errors) {
                        const msgs = Object.values(data.errors).flat();
                        this.errors = { general: msgs.join(' | ') };
                    } else {
                        this.errors = { general: data.message ?? 'Error al procesar la venta.' };
                    }
                } catch(e) {
                    this.errors = { general: 'Error de conexión. Intente nuevamente.' };
                }
                this.submitting = false;
            },

            dismissSuccess() {
                this.lastDoc    = null;
                this.lastDocUrl = null;
            },

            fmtCurrency(v) {
                return 'Bs. ' + (parseFloat(v) || 0).toFixed(2);
            },
        }"
        class="space-y-5"
    >
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
                <button @click="dismissSuccess()" class="text-emerald-400 hover:text-emerald-600 text-xl leading-none">✕</button>
            </div>
        </div>

        {{-- Banner error --}}
        <div x-show="errors.general" x-cloak
            class="flex items-center gap-3 rounded-2xl bg-red-50 border border-red-100 px-6 py-4 text-sm font-bold text-[#e11d48]">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 6L6 18M6 6l12 12"/></svg>
            <span x-text="errors.general"></span>
        </div>

        {{-- Layout POS: 3/5 izquierda + 2/5 derecha --}}
        <div class="grid gap-5 lg:grid-cols-5">

            {{-- ══ Columna izquierda — Búsqueda + Carrito ══ --}}
            <div class="space-y-5 lg:col-span-3">

                {{-- Selector de sucursal (más de una) --}}
                @if($locations->count() > 1)
                    <div class="rounded-[2.5rem] border border-gray-100 bg-white p-6 shadow-sm">
                        <label class="mb-1.5 block text-sm font-bold text-[#1e293b]">
                            Sucursal de venta <span class="text-[#e11d48]">*</span>
                        </label>
                        <div class="relative">
                            <select x-model="locationId"
                                @change="searchResults = []; searchQuery = ''; $nextTick(() => $refs.searchInput?.focus())"
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

                {{-- Buscador --}}
                <div class="rounded-[2.5rem] border border-gray-100 bg-white p-6 shadow-sm space-y-4">

                    {{-- Filtros: Categoría + Precio --}}
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                        <div class="sm:col-span-1">
                            <label class="mb-1 block text-[10px] font-black uppercase tracking-widest text-gray-400">Categoría</label>
                            <div class="relative">
                                <select x-model="categoryFilter" @change="onFilterChange()"
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
                        </div>
                        <div>
                            <label class="mb-1 block text-[10px] font-black uppercase tracking-widest text-gray-400">Precio mín. (Bs)</label>
                            <input type="number" x-model="priceMin" @input="onFilterChange()" min="0" step="0.01"
                                placeholder="0.00" :disabled="!locationId"
                                class="h-9 w-full rounded-xl border border-gray-200 bg-gray-50 px-3 text-sm text-gray-700 focus:border-gray-400 focus:outline-none transition-all disabled:opacity-40" />
                        </div>
                        <div>
                            <label class="mb-1 block text-[10px] font-black uppercase tracking-widest text-gray-400">Precio máx. (Bs)</label>
                            <input type="number" x-model="priceMax" @input="onFilterChange()" min="0" step="0.01"
                                placeholder="0.00" :disabled="!locationId"
                                class="h-9 w-full rounded-xl border border-gray-200 bg-gray-50 px-3 text-sm text-gray-700 focus:border-gray-400 focus:outline-none transition-all disabled:opacity-40" />
                        </div>
                    </div>

                    {{-- Búsqueda por texto / código de barras --}}
                    <div>
                    <label class="mb-2 block text-sm font-bold text-[#1e293b]">
                        Buscar producto
                        <span class="ml-1 text-xs font-medium text-gray-400">(nombre, precio o código de barras)</span>
                    </label>
                    <div class="relative" @click.outside="showResults = false">
                        <div class="pointer-events-none absolute inset-y-0 left-4 flex items-center">
                            <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11A6 6 0 111 11a6 6 0 0116 0z"/></svg>
                        </div>
                        <input
                            type="text"
                            x-ref="searchInput"
                            x-model="searchQuery"
                            @input="onSearchInput()"
                            @focus="if(searchQuery.length >= 2) showResults = true"
                            @keydown.enter.prevent="onSearchEnter()"
                            @keydown.escape="showResults = false; searchQuery = ''; searchResults = []"
                            placeholder="Escriba nombre o escanee código de barras..."
                            autocomplete="off"
                            :disabled="!locationId"
                            class="h-12 w-full rounded-xl border border-gray-200 bg-white py-2.5 pl-11 pr-4 text-base text-gray-700 placeholder:text-gray-400 focus:border-gray-400 focus:outline-none transition-all disabled:opacity-40 disabled:cursor-not-allowed"
                        />

                        {{-- Spinner --}}
                        <div x-show="searchLoading" class="pointer-events-none absolute inset-y-0 right-4 flex items-center">
                            <div class="h-4 w-4 animate-spin rounded-full border-2 border-[#e11d48] border-t-transparent"></div>
                        </div>

                        {{-- Resultados dropdown --}}
                        <div x-show="showResults && searchResults.length > 0" x-cloak
                            class="absolute top-full left-0 right-0 z-50 mt-1 max-h-72 overflow-y-auto rounded-xl border border-gray-100 bg-white shadow-xl">
                            <template x-for="p in searchResults" :key="p.id">
                                <button type="button" @click="addToCart(p)"
                                    class="flex w-full items-center gap-3 px-4 py-3 text-left hover:bg-gray-50 transition-colors">
                                    <div class="flex-1 min-w-0">
                                        <p class="truncate text-sm font-bold text-[#1e293b]" x-text="p.name"></p>
                                        <p class="text-xs text-gray-400">
                                            <span x-text="p.unit_of_measure || 'UN'"></span>
                                            <template x-if="p.use_batches">
                                                <span class="ml-1 rounded-lg bg-amber-50 px-1.5 py-0.5 text-[10px] font-bold uppercase text-amber-600">PEPS</span>
                                            </template>
                                            <template x-if="p.sale_price !== null">
                                                <span class="ml-2 font-bold text-[#1e293b]" x-text="'Bs ' + p.sale_price.toFixed(2)"></span>
                                            </template>
                                        </p>
                                    </div>
                                    <span class="shrink-0 rounded-lg px-2.5 py-1 text-[10px] font-bold uppercase"
                                        :class="p.stock > 0
                                            ? 'bg-emerald-50 text-emerald-600'
                                            : 'bg-red-50 text-[#e11d48]'">
                                        <span x-text="p.stock"></span> disp.
                                    </span>
                                </button>
                            </template>
                        </div>

                        {{-- Sin resultados --}}
                        <div x-show="showResults && !searchLoading && searchResults.length === 0 && searchQuery.length >= 2" x-cloak
                            class="absolute top-full left-0 right-0 z-50 mt-1 rounded-xl border border-gray-100 bg-white px-4 py-3 text-sm font-medium text-gray-400 shadow-xl">
                            Sin resultados para "<span x-text="searchQuery"></span>"
                        </div>
                    </div>
                    <p class="mt-2 text-xs text-gray-400">
                        Presione <kbd class="rounded border border-gray-200 px-1 font-mono text-xs">Enter</kbd> para agregar el primer resultado. Compatible con lectores USB.
                    </p>
                    </div>{{-- /text search wrapper --}}
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
                        <p class="mt-1 text-xs text-gray-300">Busque un producto para comenzar</p>
                    </div>

                    {{-- Líneas del carrito --}}
                    <div x-show="cart.length > 0">
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="text-[10px] font-bold uppercase tracking-widest text-gray-400">
                                        <th class="pb-3 pl-8 pt-4">Producto</th>
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
                                                <span x-text="fmtCurrency((parseFloat(line.qty)||0) * (parseFloat(line.price)||0))"></span>
                                            </td>
                                            <td class="py-4 pr-4">
                                                <button type="button" @click="removeFromCart(i)"
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

            {{-- ══ Columna derecha — Resumen + Confirmar ══ --}}
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
                                x-text="fmtCurrency(cartTotal)"></span>
                        </div>

                        {{-- Resumen líneas --}}
                        <div x-show="cart.length > 0" class="mb-5 space-y-1.5 border-t border-gray-50 pt-4">
                            <template x-for="line in cart" :key="line.product_id">
                                <div class="flex justify-between text-xs">
                                    <span class="font-medium text-gray-500" x-text="`${line.name} × ${parseFloat(line.qty)||0}`"></span>
                                    <span class="font-bold text-gray-700" x-text="fmtCurrency((parseFloat(line.qty)||0)*(parseFloat(line.price)||0))"></span>
                                </div>
                            </template>
                        </div>

                        <button type="button"
                            @click="confirmSale()"
                            :disabled="!canSubmit"
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

                    {{-- Link historial --}}
                    <a href="{{ route('sales.index') }}"
                        class="flex h-11 w-full items-center justify-center rounded-xl border border-gray-200 bg-white text-sm font-bold text-gray-500 hover:bg-gray-50 hover:text-[#1e293b] transition-all">
                        Ver Historial de Ventas
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection