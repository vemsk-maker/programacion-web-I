@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Stock Actual — Valorización" />

    {{-- Filters --}}
    <form method="GET" action="{{ route('reports.stock') }}"
          class="mb-5 flex flex-wrap items-end gap-3 rounded-2xl border border-gray-200 bg-white px-5 py-4 dark:border-gray-800 dark:bg-white/[0.03]">

        {{-- Category --}}
        <div>
            <label class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400">Categoría</label>
            <div x-data="{ isOptionSelected: {{ $categoryId ? 'true' : 'false' }} }" class="relative z-20">
                <select name="category_id" @change="isOptionSelected = true"
                    class="shadow-theme-xs h-10 w-44 appearance-none rounded-lg border border-gray-300 bg-transparent px-3 pr-8 text-sm focus:border-brand-300 focus:outline-none dark:border-gray-700 dark:bg-gray-900"
                    :class="isOptionSelected ? 'text-gray-800 dark:text-white/90' : 'text-gray-400 dark:text-gray-500'">
                    <option value="">Todas las categorías</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ $categoryId == $cat->id ? 'selected' : '' }}
                            class="text-gray-700 dark:bg-gray-900 dark:text-gray-400">{{ $cat->name }}</option>
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
                    <option value="">Todas las ubicaciones</option>
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

        <button type="submit"
            class="h-10 rounded-lg bg-brand-500 px-5 text-sm font-medium text-white hover:bg-brand-600">
            Filtrar
        </button>
        <a href="{{ route('reports.stock') }}"
            class="inline-flex h-10 items-center rounded-lg bg-gray-100 px-4 text-sm font-medium text-gray-600 hover:bg-gray-200 dark:bg-white/10 dark:text-white/80 dark:hover:bg-white/20">
            Limpiar
        </a>

        {{-- Export --}}
        <a href="{{ route('reports.stock', array_merge(request()->query(), ['export' => 1])) }}"
            class="ml-auto inline-flex h-10 items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 text-sm font-medium text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:bg-white/[0.03] dark:text-gray-400 dark:hover:bg-white/[0.06]">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
            </svg>
            Exportar CSV
        </a>
    </form>

    {{-- Valuación total (solo roles con costos) --}}
    @if($canCost && $totalValue !== null)
        <div class="mb-5 rounded-2xl border border-brand-200 bg-brand-50 px-5 py-4 dark:border-brand-500/20 dark:bg-brand-500/10">
            <div class="flex items-baseline justify-between">
                <p class="text-sm font-medium text-brand-700 dark:text-brand-400">Valorización total del inventario</p>
                <p class="text-2xl font-bold text-brand-800 dark:text-brand-300">
                    Bs {{ number_format((float) $totalValue, 2) }}
                </p>
            </div>
            @if($locationId || $categoryId)
                <p class="mt-0.5 text-xs text-brand-500 dark:text-brand-500">(Filtrado por los criterios seleccionados)</p>
            @endif
        </div>
    @endif

    {{-- Table --}}
    <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 dark:border-gray-800">
                        <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Producto</th>
                        <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Categoría</th>
                        <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Ubicación</th>
                        <th class="px-5 py-3 text-right text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Cantidad</th>
                        <th class="px-5 py-3 text-center text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">UM</th>
                        @if($canCost)
                            <th class="px-5 py-3 text-right text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Costo Unit.</th>
                            <th class="px-5 py-3 text-right text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Valor Total</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($rows as $row)
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-white/[0.02]">
                            <td class="px-5 py-3 font-medium text-gray-800 dark:text-white/90">
                                {{ $row->product }}
                            </td>
                            <td class="px-5 py-3 text-gray-500 dark:text-gray-400">
                                {{ $row->category ?? '—' }}
                            </td>
                            <td class="px-5 py-3 text-gray-600 dark:text-gray-400">
                                {{ $row->location }}
                            </td>
                            <td class="px-5 py-3 text-right font-medium text-gray-800 dark:text-white/90">
                                {{ number_format($row->total_qty, 2) }}
                            </td>
                            <td class="px-5 py-3 text-center text-xs text-gray-400 dark:text-gray-500">
                                {{ $row->unit_of_measure ?: '—' }}
                            </td>
                            @if($canCost)
                                <td class="px-5 py-3 text-right text-gray-600 dark:text-gray-400">
                                    @if($row->unit_cost)
                                        Bs {{ number_format($row->unit_cost, 2) }}
                                    @else
                                        <span class="text-gray-300 dark:text-gray-600">—</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3 text-right font-medium text-gray-800 dark:text-white/90">
                                    @if($row->unit_cost)
                                        Bs {{ number_format($row->total_qty * $row->unit_cost, 2) }}
                                    @else
                                        <span class="text-gray-300 dark:text-gray-600">—</span>
                                    @endif
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $canCost ? 7 : 5 }}" class="px-5 py-12 text-center text-sm text-gray-400 dark:text-gray-500">
                                No hay stock registrado con los filtros seleccionados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($rows->hasPages())
            <div class="border-t border-gray-100 px-5 py-4 dark:border-gray-800">
                {{ $rows->links() }}
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
