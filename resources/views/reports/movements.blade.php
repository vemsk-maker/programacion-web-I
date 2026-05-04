@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Kardex — Movimientos" />

    {{-- ── Filtros ── --}}
    <div class="rounded-[2.5rem] border border-gray-100 bg-white p-8 shadow-sm">
        <div class="mb-6 border-b border-gray-50 pb-6">
            <h3 class="text-2xl font-bold text-[#1e293b]">Kardex — Movimientos de Inventario</h3>
            <p class="text-sm text-gray-500">Historial completo de entradas, salidas y traslados</p>
        </div>

        <form method="GET" action="{{ route('reports.movements') }}"
              class="flex flex-wrap items-end gap-3">

            {{-- Producto --}}
            <div>
                <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-widest text-gray-400">Producto</label>
                <div class="relative">
                    <select name="product_id"
                        class="h-11 w-56 appearance-none rounded-xl border border-gray-200 bg-white px-4 pr-10 text-sm text-gray-700 focus:border-gray-400 focus:outline-none transition-all">
                        <option value="">Todos los productos</option>
                        @foreach($products as $prod)
                            <option value="{{ $prod->id }}" {{ $productId == $prod->id ? 'selected' : '' }}>{{ $prod->name }}</option>
                        @endforeach
                    </select>
                    <span class="pointer-events-none absolute top-1/2 right-4 -translate-y-1/2 text-gray-400">
                        <svg width="16" height="16" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M4.79175 7.396L10.0001 12.6043L15.2084 7.396" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </span>
                </div>
            </div>

            {{-- Ubicación --}}
            <div>
                <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-widest text-gray-400">Ubicación</label>
                <div class="relative">
                    <select name="location_id"
                        class="h-11 w-48 appearance-none rounded-xl border border-gray-200 bg-white px-4 pr-10 text-sm text-gray-700 focus:border-gray-400 focus:outline-none transition-all">
                        <option value="">Todas</option>
                        @foreach($locations as $loc)
                            <option value="{{ $loc->id }}" {{ $locationId == $loc->id ? 'selected' : '' }}>{{ $loc->name }}</option>
                        @endforeach
                    </select>
                    <span class="pointer-events-none absolute top-1/2 right-4 -translate-y-1/2 text-gray-400">
                        <svg width="16" height="16" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M4.79175 7.396L10.0001 12.6043L15.2084 7.396" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </span>
                </div>
            </div>

            {{-- Tipo --}}
            <div>
                <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-widest text-gray-400">Tipo</label>
                <div class="relative">
                    <select name="type"
                        class="h-11 w-40 appearance-none rounded-xl border border-gray-200 bg-white px-4 pr-10 text-sm text-gray-700 focus:border-gray-400 focus:outline-none transition-all">
                        <option value="">Todos</option>
                        @foreach($types as $t)
                            <option value="{{ $t->value }}" {{ $type === $t->value ? 'selected' : '' }}>
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
                    <span class="pointer-events-none absolute top-1/2 right-4 -translate-y-1/2 text-gray-400">
                        <svg width="16" height="16" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M4.79175 7.396L10.0001 12.6043L15.2084 7.396" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </span>
                </div>
            </div>

            {{-- Rango de fechas --}}
            <div>
                <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-widest text-gray-400">Desde / Hasta</label>
                <div class="flex items-center gap-2 rounded-xl border border-gray-200 bg-gray-50/50 px-3">
                    <input type="date" name="date_from" value="{{ $dateFrom }}"
                        class="h-10 bg-transparent text-sm text-gray-700 focus:outline-none" />
                    <span class="text-gray-400">/</span>
                    <input type="date" name="date_to" value="{{ $dateTo }}"
                        class="h-10 bg-transparent text-sm text-gray-700 focus:outline-none" />
                </div>
            </div>

            <button type="submit" class="h-11 rounded-xl bg-[#1e293b] px-6 text-sm font-bold text-white hover:bg-[#334155] transition-all">
                Filtrar
            </button>
            <a href="{{ route('reports.movements') }}" class="text-sm font-bold text-gray-400 hover:text-[#e11d48] transition-colors">
                Limpiar
            </a>

            <a href="{{ route('reports.movements', array_merge(request()->query(), ['export' => 1])) }}"
                class="ml-auto flex h-11 items-center gap-2 rounded-xl border border-gray-200 bg-white px-5 text-sm font-bold text-gray-500 hover:bg-gray-50 transition-all">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                Exportar CSV
            </a>
        </form>
    </div>

    {{-- Leyenda de tipos --}}
    <div class="my-5 flex flex-wrap items-center gap-2">
        <span class="text-[10px] font-bold uppercase tracking-widest text-gray-400">Tipos:</span>
        <span class="inline-flex items-center rounded-lg bg-emerald-50 px-3 py-1 text-[10px] font-black uppercase text-emerald-600">Compra</span>
        <span class="inline-flex items-center rounded-lg bg-blue-50 px-3 py-1 text-[10px] font-black uppercase text-blue-600">Venta</span>
        <span class="inline-flex items-center rounded-lg bg-amber-50 px-3 py-1 text-[10px] font-black uppercase text-amber-600">Traslado</span>
        <span class="inline-flex items-center rounded-lg bg-gray-100 px-3 py-1 text-[10px] font-black uppercase text-gray-500">Ajuste</span>
        <span class="inline-flex items-center rounded-lg bg-red-50 px-3 py-1 text-[10px] font-black uppercase text-[#e11d48]">Baja</span>
    </div>

    {{-- Tabla --}}
    <div class="rounded-[2.5rem] border border-gray-100 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="text-[10px] font-bold uppercase tracking-widest text-gray-400">
                        <th class="pb-4 pl-8 pt-6">Fecha</th>
                        <th class="pb-4 pt-6">Tipo</th>
                        <th class="pb-4 pt-6">Producto</th>
                        <th class="pb-4 pt-6">Lote</th>
                        <th class="pb-4 pt-6">Origen</th>
                        <th class="pb-4 pt-6">Destino</th>
                        <th class="pb-4 pt-6 text-right">Cantidad</th>
                        @if($canCost)
                            <th class="pb-4 pt-6 text-right">Costo Unit.</th>
                        @endif
                        <th class="pb-4 pt-6">Usuario</th>
                        <th class="pb-4 pr-8 pt-6">Referencia</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 border-t border-gray-50">
                    @forelse($movements as $m)
                        @php
                            $movType = $m->group?->type;
                            $badge   = match($movType?->value ?? '') {
                                'purchase'   => 'bg-emerald-50 text-emerald-600',
                                'sale'       => 'bg-blue-50 text-blue-600',
                                'transfer'   => 'bg-amber-50 text-amber-600',
                                'adjustment' => 'bg-gray-100 text-gray-500',
                                'waste'      => 'bg-red-50 text-[#e11d48]',
                                default      => 'bg-gray-100 text-gray-400',
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
                        <tr class="group transition-colors hover:bg-gray-50/50">
                            <td class="py-4 pl-8">
                                <span class="text-sm font-bold text-[#1e293b]">{{ $m->created_at->format('d/m/Y') }}</span>
                                <span class="block text-[10px] text-gray-400">{{ $m->created_at->format('H:i') }}</span>
                            </td>
                            <td class="py-4">
                                <span class="inline-flex items-center rounded-lg px-2.5 py-1 text-[10px] font-black uppercase {{ $badge }}">
                                    {{ $label }}
                                </span>
                            </td>
                            <td class="py-4 text-sm font-bold text-[#1e293b]">
                                {{ $m->product?->name ?? '—' }}
                            </td>
                            <td class="py-4 font-mono text-xs font-bold text-gray-400">
                                {{ $m->batch?->batch_code ?? '—' }}
                            </td>
                            <td class="py-4 text-sm text-gray-500">
                                {{ $m->fromLocation?->name ?? '—' }}
                            </td>
                            <td class="py-4 text-sm text-gray-500">
                                {{ $m->toLocation?->name ?? '—' }}
                            </td>
                            <td class="py-4 text-right font-black text-[#1e293b]">
                                {{ number_format($m->quantity, 2) }}
                            </td>
                            @if($canCost)
                                <td class="py-4 text-right font-bold text-[#e11d48]">
                                    {{ $m->unit_cost ? 'Bs. ' . number_format($m->unit_cost, 2) : '—' }}
                                </td>
                            @endif
                            <td class="py-4 text-xs font-medium text-gray-500">
                                {{ $m->group?->user?->name ?? '—' }}
                            </td>
                            <td class="py-4 pr-8 font-mono text-xs text-gray-400">
                                {{ $m->group?->reference_doc ?? '—' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $canCost ? 10 : 9 }}" class="py-20 text-center">
                                <p class="text-sm font-medium italic text-gray-400">No hay movimientos con los filtros seleccionados.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($movements->hasPages())
            <div class="border-t border-gray-50 px-8 py-6">
                {{ $movements->links() }}
            </div>
        @endif
    </div>

    <div class="mt-8">
        <a href="{{ route('reports.index') }}"
           class="inline-flex items-center gap-2 rounded-2xl bg-gray-100 px-8 py-3 text-sm font-bold text-gray-500 transition-all hover:bg-gray-200 hover:text-[#1e293b]">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
            Volver a Reportes
        </a>
    </div>
@endsection