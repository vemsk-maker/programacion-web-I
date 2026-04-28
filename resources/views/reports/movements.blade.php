@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Kardex — Movimientos de Inventario" />

    {{-- Filters --}}
    <form method="GET" action="{{ route('reports.movements') }}"
          class="mb-5 flex flex-wrap items-end gap-3 rounded-2xl border border-gray-200 bg-white px-5 py-4 dark:border-gray-800 dark:bg-white/[0.03]">

        {{-- Product search --}}
        <div>
            <label class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400">Producto</label>
            <div x-data="{ isOptionSelected: {{ $productId ? 'true' : 'false' }} }" class="relative z-20">
                <select name="product_id" @change="isOptionSelected = true"
                    class="shadow-theme-xs h-10 w-52 appearance-none rounded-lg border border-gray-300 bg-transparent px-3 pr-8 text-sm focus:border-brand-300 focus:outline-none dark:border-gray-700 dark:bg-gray-900"
                    :class="isOptionSelected ? 'text-gray-800 dark:text-white/90' : 'text-gray-400 dark:text-gray-500'">
                    <option value="">Todos los productos</option>
                    @foreach($products as $prod)
                        <option value="{{ $prod->id }}" {{ $productId == $prod->id ? 'selected' : '' }}
                            class="text-gray-700 dark:bg-gray-900 dark:text-gray-400">{{ $prod->name }}</option>
                    @endforeach
                </select>
                <span class="pointer-events-none absolute top-1/2 right-2.5 z-30 -translate-y-1/2 text-gray-400">
                    <svg class="stroke-current" width="16" height="16" viewBox="0 0 20 20" fill="none"><path d="M4.79175 7.396L10.0001 12.6043L15.2084 7.396" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </span>
            </div>
        </div>

        {{-- Location --}}
        <div>
            <label class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400">Ubicación</label>
            <div x-data="{ isOptionSelected: {{ $locationId ? 'true' : 'false' }} }" class="relative z-20">
                <select name="location_id" @change="isOptionSelected = true"
                    class="shadow-theme-xs h-10 w-44 appearance-none rounded-lg border border-gray-300 bg-transparent px-3 pr-8 text-sm focus:border-brand-300 focus:outline-none dark:border-gray-700 dark:bg-gray-900"
                    :class="isOptionSelected ? 'text-gray-800 dark:text-white/90' : 'text-gray-400 dark:text-gray-500'">
                    <option value="">Todas</option>
                    @foreach($locations as $loc)
                        <option value="{{ $loc->id }}" {{ $locationId == $loc->id ? 'selected' : '' }}
                            class="text-gray-700 dark:bg-gray-900 dark:text-gray-400">{{ $loc->name }}</option>
                    @endforeach
                </select>
                <span class="pointer-events-none absolute top-1/2 right-2.5 z-30 -translate-y-1/2 text-gray-400">
                    <svg class="stroke-current" width="16" height="16" viewBox="0 0 20 20" fill="none"><path d="M4.79175 7.396L10.0001 12.6043L15.2084 7.396" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </span>
            </div>
        </div>

        {{-- Type --}}
        <div>
            <label class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400">Tipo</label>
            <div x-data="{ isOptionSelected: {{ $type ? 'true' : 'false' }} }" class="relative z-20">
                <select name="type" @change="isOptionSelected = true"
                    class="shadow-theme-xs h-10 w-36 appearance-none rounded-lg border border-gray-300 bg-transparent px-3 pr-8 text-sm focus:border-brand-300 focus:outline-none dark:border-gray-700 dark:bg-gray-900"
                    :class="isOptionSelected ? 'text-gray-800 dark:text-white/90' : 'text-gray-400 dark:text-gray-500'">
                    <option value="">Todos</option>
                    @foreach($types as $t)
                        <option value="{{ $t->value }}" {{ $type === $t->value ? 'selected' : '' }}
                            class="text-gray-700 dark:bg-gray-900 dark:text-gray-400">
                            {{ match($t) {
                                \App\Enums\MovementType::Purchase   => 'Compra',
                                \App\Enums\MovementType::Sale       => 'Venta',
                                \App\Enums\MovementType::Transfer   => 'Traslado',
                                \App\Enums\MovementType::Adjustment => 'Ajuste',
                                \App\Enums\MovementType::Waste      => 'Baja',
                            } }}
                        </option>
                    @endforeach
                </select>
                <span class="pointer-events-none absolute top-1/2 right-2.5 z-30 -translate-y-1/2 text-gray-400">
                    <svg class="stroke-current" width="16" height="16" viewBox="0 0 20 20" fill="none"><path d="M4.79175 7.396L10.0001 12.6043L15.2084 7.396" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </span>
            </div>
        </div>

        {{-- Date range --}}
        <div>
            <label class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400">Desde</label>
            <input type="date" name="date_from" value="{{ $dateFrom }}"
                class="shadow-theme-xs h-10 rounded-lg border border-gray-300 bg-transparent px-3 text-sm text-gray-800 focus:border-brand-300 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
        </div>
        <div>
            <label class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400">Hasta</label>
            <input type="date" name="date_to" value="{{ $dateTo }}"
                class="shadow-theme-xs h-10 rounded-lg border border-gray-300 bg-transparent px-3 text-sm text-gray-800 focus:border-brand-300 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
        </div>

        <button type="submit"
            class="h-10 rounded-lg bg-brand-500 px-5 text-sm font-medium text-white hover:bg-brand-600">
            Filtrar
        </button>
        <a href="{{ route('reports.movements') }}"
            class="inline-flex h-10 items-center rounded-lg bg-gray-100 px-4 text-sm font-medium text-gray-600 hover:bg-gray-200 dark:bg-white/10 dark:text-white/80 dark:hover:bg-white/20">
            Limpiar
        </a>

        {{-- Export --}}
        <a href="{{ route('reports.movements', array_merge(request()->query(), ['export' => 1])) }}"
            class="ml-auto inline-flex h-10 items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 text-sm font-medium text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:bg-white/[0.03] dark:text-gray-400 dark:hover:bg-white/[0.06]">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
            </svg>
            Exportar CSV
        </a>
    </form>

    {{-- Type badge legend --}}
    <div class="mb-4 flex flex-wrap items-center gap-2 text-xs">
        <span class="font-medium text-gray-500 dark:text-gray-400">Tipos:</span>
        <span class="inline-flex items-center rounded-full bg-success-50 px-2.5 py-0.5 font-medium text-success-700 dark:bg-success-500/10 dark:text-success-400">Compra</span>
        <span class="inline-flex items-center rounded-full bg-blue-50 px-2.5 py-0.5 font-medium text-blue-700 dark:bg-blue-500/10 dark:text-blue-400">Venta</span>
        <span class="inline-flex items-center rounded-full bg-warning-50 px-2.5 py-0.5 font-medium text-warning-700 dark:bg-warning-500/10 dark:text-warning-400">Traslado</span>
        <span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 font-medium text-gray-600 dark:bg-white/10 dark:text-gray-400">Ajuste</span>
        <span class="inline-flex items-center rounded-full bg-error-50 px-2.5 py-0.5 font-medium text-error-600 dark:bg-error-500/10 dark:text-error-400">Baja</span>
    </div>

    {{-- Table --}}
    <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 dark:border-gray-800">
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Fecha</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Tipo</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Producto</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Lote</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Origen</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Destino</th>
                        <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Cantidad</th>
                        @if($canCost)
                            <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Costo Unit.</th>
                        @endif
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Usuario</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Referencia</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($movements as $m)
                        @php
                            $movType = $m->group?->type;
                            $badge   = match($movType?->value ?? '') {
                                'purchase'   => 'bg-success-50 text-success-700 dark:bg-success-500/10 dark:text-success-400',
                                'sale'       => 'bg-blue-50 text-blue-700 dark:bg-blue-500/10 dark:text-blue-400',
                                'transfer'   => 'bg-warning-50 text-warning-700 dark:bg-warning-500/10 dark:text-warning-400',
                                'adjustment' => 'bg-gray-100 text-gray-600 dark:bg-white/10 dark:text-gray-400',
                                'waste'      => 'bg-error-50 text-error-600 dark:bg-error-500/10 dark:text-error-400',
                                default      => 'bg-gray-100 text-gray-500 dark:bg-white/10 dark:text-gray-400',
                            };
                            $label = match($movType?->value ?? '') {
                                'purchase'   => 'Compra',
                                'sale'       => 'Venta',
                                'transfer'   => 'Traslado',
                                'adjustment' => 'Ajuste',
                                'waste'      => 'Baja',
                                default      => $movType?->value ?? '—',
                            };
                        @endphp
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-white/[0.02]">
                            <td class="px-4 py-3 text-xs text-gray-500 dark:text-gray-400">
                                {{ $m->created_at->format('d/m/Y') }}
                                <span class="block text-gray-400">{{ $m->created_at->format('H:i') }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $badge }}">
                                    {{ $label }}
                                </span>
                            </td>
                            <td class="px-4 py-3 font-medium text-gray-800 dark:text-white/90">
                                {{ $m->product?->name ?? '—' }}
                            </td>
                            <td class="px-4 py-3 font-mono text-xs text-gray-500 dark:text-gray-400">
                                {{ $m->batch?->batch_code ?? '—' }}
                            </td>
                            <td class="px-4 py-3 text-gray-600 dark:text-gray-400">
                                {{ $m->fromLocation?->name ?? '—' }}
                            </td>
                            <td class="px-4 py-3 text-gray-600 dark:text-gray-400">
                                {{ $m->toLocation?->name ?? '—' }}
                            </td>
                            <td class="px-4 py-3 text-right font-medium text-gray-800 dark:text-white/90">
                                {{ number_format($m->quantity, 2) }}
                            </td>
                            @if($canCost)
                                <td class="px-4 py-3 text-right text-gray-600 dark:text-gray-400">
                                    {{ $m->unit_cost ? 'Bs ' . number_format($m->unit_cost, 2) : '—' }}
                                </td>
                            @endif
                            <td class="px-4 py-3 text-xs text-gray-500 dark:text-gray-400">
                                {{ $m->group?->user?->name ?? '—' }}
                            </td>
                            <td class="px-4 py-3 font-mono text-xs text-gray-400 dark:text-gray-500">
                                {{ $m->group?->reference_doc ?? '—' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $canCost ? 10 : 9 }}" class="px-5 py-12 text-center text-sm text-gray-400 dark:text-gray-500">
                                No hay movimientos con los filtros seleccionados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($movements->hasPages())
            <div class="border-t border-gray-100 px-5 py-4 dark:border-gray-800">
                {{ $movements->links() }}
            </div>
        @endif
    </div>

    <div class="mt-4">
        <a href="{{ route('reports.index') }}"
            class="rounded-lg bg-gray-100 px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-200 dark:bg-white/10 dark:text-white/80 dark:hover:bg-white/20">
            ← Volver a Reportes
        </a>
    </div>
@endsection
