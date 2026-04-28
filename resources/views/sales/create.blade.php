@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Punto de Venta" />

    <div
        x-data="{
            // ── State ────────────────────────────────────────────────────────────────
            locationId:    '{{ $locations->count() === 1 ? $locations->first()->id : '' }}',
            searchQuery:   '',
            searchResults: [],
            searchLoading: false,
            searchTimer:   null,
            showResults:   false,

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

            // ── Computed ─────────────────────────────────────────────────────────────
            get cartTotal() {
                return this.cart.reduce((s, l) => s + (parseFloat(l.qty) || 0) * (parseFloat(l.price) || 0), 0);
            },
            get canSubmit() {
                return this.cart.length > 0 && !!this.locationId && !this.submitting;
            },

            // ── Init ─────────────────────────────────────────────────────────────────
            init() {
                this.csrfToken = document.querySelector('meta[name=csrf-token]')?.content ?? '';
                this.$nextTick(() => this.$refs.searchInput?.focus());
            },

            // ── Search ───────────────────────────────────────────────────────────────
            onSearchInput() {
                clearTimeout(this.searchTimer);
                this.errors = {};
                const q = this.searchQuery.trim();
                if (q.length < 2) { this.searchResults = []; this.showResults = false; return; }
                this.searchTimer = setTimeout(() => this.doSearch(q), 300);
            },

            async doSearch(q) {
                if (!this.locationId) return;
                this.searchLoading = true;
                this.showResults   = true;
                const params = new URLSearchParams({ q, location_id: this.locationId });
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

            // Handle keyboard navigation: Enter on first result
            onSearchEnter() {
                if (this.searchResults.length > 0) {
                    this.addToCart(this.searchResults[0]);
                }
            },

            // ── Cart ─────────────────────────────────────────────────────────────────
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
                        price:       0,
                    });
                }
                this.searchQuery  = '';
                this.searchResults = [];
                this.showResults  = false;
                this.errors = {};
                this.$nextTick(() => this.$refs.searchInput?.focus());
            },

            removeFromCart(i) {
                this.cart.splice(i, 1);
            },

            clearCart() {
                this.cart      = [];
                this.clientName = '';
                this.clientNit  = '';
                this.errors    = {};
                this.$nextTick(() => this.$refs.searchInput?.focus());
            },

            // ── Submit ───────────────────────────────────────────────────────────────
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
                            client_name: this.clientName  || null,
                            client_nit:  this.clientNit   || null,
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
                        this.cart        = [];
                        this.clientName  = '';
                        this.clientNit   = '';
                        this.$nextTick(() => this.$refs.searchInput?.focus());
                    } else if (r.status === 422 && data.errors) {
                        // Flatten validation errors for display
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
                return 'Bs ' + (parseFloat(v) || 0).toFixed(2);
            },
        }"
        class="space-y-0"
    >
        {{-- ── Success banner ── --}}
        <div x-show="lastDoc" x-cloak
            class="mb-4 flex items-center justify-between rounded-lg bg-success-50 border border-success-200 px-5 py-3 dark:bg-success-500/10 dark:border-success-500/20">
            <div>
                <p class="text-sm font-medium text-success-700 dark:text-success-400">
                    ✓ Venta registrada — Recibo <span x-text="lastDoc" class="font-mono font-bold"></span>
                </p>
            </div>
            <div class="flex items-center gap-3">
                <a :href="lastDocUrl" target="_blank"
                    class="rounded-lg bg-success-600 px-4 py-1.5 text-xs font-medium text-white hover:bg-success-700">
                    Ver / Imprimir
                </a>
                <button @click="dismissSuccess()"
                    class="text-success-500 hover:text-success-700 text-lg leading-none">✕</button>
            </div>
        </div>

        {{-- ── Error banner ── --}}
        <div x-show="errors.general" x-cloak
            class="mb-4 rounded-lg bg-error-50 border border-error-200 px-5 py-3 text-sm text-error-700 dark:bg-error-500/10 dark:border-error-500/20 dark:text-error-400">
            <span x-text="errors.general"></span>
        </div>

        {{-- ── Two-column POS layout ── --}}
        <div class="grid gap-5 lg:grid-cols-5">

            {{-- ════════ Left column (3/5) — Search + Cart ════════ --}}
            <div class="space-y-5 lg:col-span-3">

                {{-- Location selector (only when more than one) --}}
                @if($locations->count() > 1)
                    <div class="rounded-2xl border border-gray-200 bg-white px-5 py-4 dark:border-gray-800 dark:bg-white/[0.03]">
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                            Sucursal de venta <span class="text-error-500">*</span>
                        </label>
                        <div x-data="{ isOptionSelected: locationId !== '' }" class="relative z-20">
                            <select x-model="locationId"
                                @change="isOptionSelected = true; searchResults = []; searchQuery = ''; $nextTick(() => $refs.searchInput?.focus())"
                                class="shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 pr-11 text-sm focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900"
                                :class="isOptionSelected ? 'text-gray-800 dark:text-white/90' : 'text-gray-400 dark:text-gray-500'">
                                <option value="">— Seleccionar sucursal —</option>
                                @foreach($locations as $loc)
                                    <option value="{{ $loc->id }}" class="text-gray-700 dark:bg-gray-900 dark:text-gray-400">{{ $loc->name }}</option>
                                @endforeach
                            </select>
                            <span class="pointer-events-none absolute top-1/2 right-4 z-30 -translate-y-1/2 text-gray-500 dark:text-gray-400">
                                <svg class="stroke-current" width="20" height="20" viewBox="0 0 20 20" fill="none"><path d="M4.79175 7.396L10.0001 12.6043L15.2084 7.396" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            </span>
                        </div>
                    </div>
                @else
                    {{-- Single location: silently pre-selected --}}
                    @if($locations->count() === 1)
                        <div class="rounded-2xl border border-brand-100 bg-brand-50 px-5 py-3 dark:border-brand-800/40 dark:bg-brand-500/10">
                            <p class="text-sm text-brand-700 dark:text-brand-400">
                                Sucursal: <span class="font-semibold">{{ $locations->first()->name }}</span>
                            </p>
                        </div>
                    @endif
                @endif

                {{-- Search box --}}
                <div class="rounded-2xl border border-gray-200 bg-white px-5 py-4 dark:border-gray-800 dark:bg-white/[0.03]">
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-400">
                        Buscar producto
                        <span class="ml-1 text-xs font-normal text-gray-400 dark:text-gray-500">(nombre o código de barras)</span>
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
                            class="shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-12 w-full rounded-xl border border-gray-300 bg-transparent py-2.5 pl-11 pr-4 text-base text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden disabled:opacity-40 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30"
                        />
                        {{-- Loading spinner --}}
                        <div x-show="searchLoading" class="pointer-events-none absolute inset-y-0 right-4 flex items-center">
                            <div class="h-4 w-4 animate-spin rounded-full border-2 border-brand-500 border-t-transparent"></div>
                        </div>

                        {{-- Results dropdown --}}
                        <div x-show="showResults && searchResults.length > 0" x-cloak
                            class="absolute top-full left-0 right-0 z-50 mt-1 max-h-72 overflow-y-auto rounded-xl border border-gray-200 bg-white shadow-xl dark:border-gray-700 dark:bg-gray-800">
                            <template x-for="p in searchResults" :key="p.id">
                                <button type="button"
                                    @click="addToCart(p)"
                                    class="flex w-full items-center gap-3 px-4 py-3 text-left hover:bg-gray-50 dark:hover:bg-white/[0.05]">
                                    <div class="flex-1 min-w-0">
                                        <p class="truncate text-sm font-medium text-gray-800 dark:text-white/90" x-text="p.name"></p>
                                        <p class="text-xs text-gray-400 dark:text-gray-500">
                                            <span x-text="p.unit_of_measure || 'UN'"></span>
                                            <template x-if="p.use_batches">
                                                <span class="ml-1 rounded bg-amber-100 px-1 py-0.5 text-amber-700 dark:bg-amber-500/10 dark:text-amber-400">PEPS</span>
                                            </template>
                                        </p>
                                    </div>
                                    <span class="shrink-0 rounded-full px-2.5 py-0.5 text-xs font-medium"
                                        :class="p.stock > 0
                                            ? 'bg-success-50 text-success-700 dark:bg-success-500/10 dark:text-success-400'
                                            : 'bg-error-50 text-error-600 dark:bg-error-500/10 dark:text-error-400'">
                                        <span x-text="p.stock"></span> disp.
                                    </span>
                                </button>
                            </template>
                        </div>

                        {{-- No results --}}
                        <div x-show="showResults && !searchLoading && searchResults.length === 0 && searchQuery.length >= 2" x-cloak
                            class="absolute top-full left-0 right-0 z-50 mt-1 rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-400 shadow-xl dark:border-gray-700 dark:bg-gray-800">
                            Sin resultados para "<span x-text="searchQuery"></span>"
                        </div>
                    </div>
                    <p class="mt-1.5 text-xs text-gray-400 dark:text-gray-500">
                        Presione <kbd class="rounded border border-gray-300 px-1 font-mono text-xs dark:border-gray-700">Enter</kbd> para agregar el primer resultado.
                        Compatible con lectores de código de barras USB.
                    </p>
                </div>

                {{-- Cart --}}
                <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
                    <div class="flex items-center justify-between border-b border-gray-100 px-5 py-3.5 dark:border-gray-800">
                        <h3 class="text-sm font-medium text-gray-800 dark:text-white/90">
                            Carrito
                            <span x-show="cart.length > 0" class="ml-1.5 inline-flex items-center rounded-full bg-brand-500 px-2 py-0.5 text-xs font-medium text-white" x-text="cart.length"></span>
                        </h3>
                        <button type="button" @click="clearCart()"
                            x-show="cart.length > 0"
                            class="text-xs text-error-500 hover:text-error-600 dark:text-error-400">
                            Limpiar carrito
                        </button>
                    </div>

                    {{-- Empty state --}}
                    <div x-show="cart.length === 0"
                        class="flex flex-col items-center justify-center py-12 text-center">
                        <svg class="mb-3 h-12 w-12 text-gray-200 dark:text-gray-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                        <p class="text-sm text-gray-400 dark:text-gray-500">El carrito está vacío</p>
                        <p class="mt-1 text-xs text-gray-300 dark:text-gray-600">Busque un producto para comenzar</p>
                    </div>

                    {{-- Cart lines --}}
                    <div x-show="cart.length > 0">
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="border-b border-gray-100 dark:border-gray-800">
                                        <th class="px-5 py-2.5 text-left text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Producto</th>
                                        <th class="px-3 py-2.5 text-center text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">UM</th>
                                        <th class="px-3 py-2.5 text-center text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400 w-24">Cant.</th>
                                        <th class="px-3 py-2.5 text-center text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400 w-28">Precio</th>
                                        <th class="px-3 py-2.5 text-right text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Subtotal</th>
                                        <th class="px-3 py-2.5 w-8"></th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                    <template x-for="(line, i) in cart" :key="i">
                                        <tr class="hover:bg-gray-50/50 dark:hover:bg-white/[0.01]">
                                            <td class="px-5 py-2.5">
                                                <p class="font-medium text-gray-800 dark:text-white/90" x-text="line.name"></p>
                                                <p x-show="line.use_batches" class="text-xs text-amber-600 dark:text-amber-400">Lote automático (PEPS)</p>
                                                <p x-show="parseFloat(line.qty) > line.stock && line.stock >= 0"
                                                    class="text-xs text-error-500">⚠ Supera stock disponible (<span x-text="line.stock"></span>)</p>
                                            </td>
                                            <td class="px-3 py-2.5 text-center text-xs text-gray-500 dark:text-gray-400" x-text="line.unit || '—'"></td>
                                            <td class="px-3 py-2.5">
                                                <input type="number" step="0.01" min="0.01"
                                                    x-model="line.qty"
                                                    class="h-9 w-full rounded-lg border border-gray-300 bg-transparent px-2 text-center text-sm text-gray-800 focus:border-brand-300 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
                                            </td>
                                            <td class="px-3 py-2.5">
                                                <div class="relative">
                                                    <span class="pointer-events-none absolute inset-y-0 left-2.5 flex items-center text-xs text-gray-400">Bs</span>
                                                    <input type="number" step="0.01" min="0"
                                                        x-model="line.price"
                                                        class="h-9 w-full rounded-lg border border-gray-300 bg-transparent pl-7 pr-2 text-right text-sm text-gray-800 focus:border-brand-300 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
                                                </div>
                                            </td>
                                            <td class="px-3 py-2.5 text-right font-medium text-gray-800 dark:text-white/90">
                                                <span x-text="fmtCurrency((parseFloat(line.qty)||0) * (parseFloat(line.price)||0))"></span>
                                            </td>
                                            <td class="px-3 py-2.5">
                                                <button type="button" @click="removeFromCart(i)"
                                                    class="flex h-7 w-7 items-center justify-center rounded-full text-gray-400 hover:bg-error-50 hover:text-error-500 dark:hover:bg-error-500/10">✕</button>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ════════ Right column (2/5) — Summary + Confirm ════════ --}}
            <div class="lg:col-span-2">
                <div class="sticky top-5 space-y-4">

                    {{-- Client info --}}
                    <div class="rounded-2xl border border-gray-200 bg-white px-5 py-4 dark:border-gray-800 dark:bg-white/[0.03]">
                        <h3 class="mb-3 text-sm font-medium text-gray-700 dark:text-gray-400">Cliente <span class="text-xs font-normal text-gray-400">(opcional)</span></h3>
                        <div class="space-y-3">
                            <div>
                                <label class="mb-1 block text-xs text-gray-500 dark:text-gray-400">Nombre</label>
                                <input type="text" x-model="clientName" placeholder="Nombre del cliente..."
                                    class="shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-10 w-full rounded-lg border border-gray-300 bg-transparent px-3 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30" />
                            </div>
                            <div>
                                <label class="mb-1 block text-xs text-gray-500 dark:text-gray-400">NIT / CI</label>
                                <input type="text" x-model="clientNit" placeholder="NIT o carnet..."
                                    class="shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-10 w-full rounded-lg border border-gray-300 bg-transparent px-3 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30" />
                            </div>
                        </div>
                    </div>

                    {{-- Total + confirm --}}
                    <div class="rounded-2xl border border-gray-200 bg-white px-5 py-5 dark:border-gray-800 dark:bg-white/[0.03]">
                        <div class="mb-4 flex items-baseline justify-between">
                            <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Total</span>
                            <span class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white"
                                x-text="fmtCurrency(cartTotal)"></span>
                        </div>

                        <div x-show="cart.length > 0" class="mb-3 space-y-1 text-xs text-gray-400 dark:text-gray-500">
                            <template x-for="line in cart" :key="line.product_id">
                                <div class="flex justify-between">
                                    <span x-text="`${line.name} × ${parseFloat(line.qty)||0}`"></span>
                                    <span x-text="fmtCurrency((parseFloat(line.qty)||0)*(parseFloat(line.price)||0))"></span>
                                </div>
                            </template>
                        </div>

                        <button type="button"
                            @click="confirmSale()"
                            :disabled="!canSubmit"
                            class="relative w-full rounded-xl bg-brand-500 px-5 py-3.5 text-base font-semibold text-white transition hover:bg-brand-600 focus:outline-none disabled:opacity-50 disabled:cursor-not-allowed">
                            <span x-show="!submitting">Confirmar Venta</span>
                            <span x-show="submitting" class="flex items-center justify-center gap-2">
                                <span class="inline-block h-4 w-4 animate-spin rounded-full border-2 border-white border-t-transparent"></span>
                                Procesando...
                            </span>
                        </button>

                        <div x-show="!locationId" class="mt-2 text-center text-xs text-warning-600 dark:text-warning-400">
                            Seleccione una sucursal para habilitar la venta
                        </div>
                        <div x-show="locationId && cart.length === 0" class="mt-2 text-center text-xs text-gray-400 dark:text-gray-500">
                            Agregue productos al carrito para continuar
                        </div>
                    </div>

                    {{-- Quick links --}}
                    <div class="flex gap-2">
                        <a href="{{ route('sales.index') }}"
                            class="flex-1 rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-center text-xs font-medium text-gray-600 hover:bg-gray-50 dark:border-gray-800 dark:bg-white/[0.03] dark:text-gray-400 dark:hover:bg-white/[0.06]">
                            Historial
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
