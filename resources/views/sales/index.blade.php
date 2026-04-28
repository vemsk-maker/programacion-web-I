@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Historial de Ventas" />

    {{-- Flash --}}
    @if(session('success'))
        <div class="mb-4 rounded-lg bg-success-50 border border-success-200 px-4 py-3 text-sm text-success-700 dark:bg-success-500/10 dark:border-success-500/20 dark:text-success-400">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="mb-4 rounded-lg bg-error-50 border border-error-200 px-4 py-3 text-sm text-error-700 dark:bg-error-500/10 dark:border-error-500/20 dark:text-error-400">
            {{ session('error') }}
        </div>
    @endif

    {{-- Filters --}}
    <form method="GET" action="{{ route('sales.index') }}"
          class="mb-5 flex flex-wrap items-end gap-3 rounded-2xl border border-gray-200 bg-white px-5 py-4 dark:border-gray-800 dark:bg-white/[0.03]">

        <div>
            <label class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400">Desde</label>
            <input type="date" name="date_from" value="{{ request('date_from', today()->toDateString()) }}"
                class="shadow-theme-xs h-10 rounded-lg border border-gray-300 bg-transparent px-3 text-sm text-gray-800 focus:border-brand-300 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
        </div>

        <div>
            <label class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400">Hasta</label>
            <input type="date" name="date_to" value="{{ request('date_to') }}"
                class="shadow-theme-xs h-10 rounded-lg border border-gray-300 bg-transparent px-3 text-sm text-gray-800 focus:border-brand-300 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
        </div>

        @if($locations->isNotEmpty())
            <div>
                <label class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400">Sucursal</label>
                <div x-data="{ isOptionSelected: {{ request('location_id') ? 'true' : 'false' }} }" class="relative z-20">
                    <select name="location_id" @change="isOptionSelected = true"
                        class="shadow-theme-xs h-10 w-44 appearance-none rounded-lg border border-gray-300 bg-transparent px-3 pr-8 text-sm focus:border-brand-300 focus:outline-none dark:border-gray-700 dark:bg-gray-900"
                        :class="isOptionSelected ? 'text-gray-800 dark:text-white/90' : 'text-gray-400 dark:text-gray-500'">
                        <option value="">Todas</option>
                        @foreach($locations as $loc)
                            <option value="{{ $loc->id }}" {{ request('location_id') == $loc->id ? 'selected' : '' }}
                                class="text-gray-700 dark:bg-gray-900 dark:text-gray-400">{{ $loc->name }}</option>
                        @endforeach
                    </select>
                    <span class="pointer-events-none absolute top-1/2 right-2.5 z-30 -translate-y-1/2 text-gray-400">
                        <svg class="stroke-current" width="16" height="16" viewBox="0 0 20 20" fill="none"><path d="M4.79175 7.396L10.0001 12.6043L15.2084 7.396" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </span>
                </div>
            </div>
        @endif

        <button type="submit"
            class="h-10 rounded-lg bg-brand-500 px-5 text-sm font-medium text-white hover:bg-brand-600">
            Filtrar
        </button>
        <a href="{{ route('sales.index') }}"
            class="h-10 inline-flex items-center rounded-lg bg-gray-100 px-4 text-sm font-medium text-gray-600 hover:bg-gray-200 dark:bg-white/10 dark:text-white/80 dark:hover:bg-white/20">
            Limpiar
        </a>

        <div class="ml-auto">
            <a href="{{ route('sales.create') }}"
                class="h-10 inline-flex items-center gap-2 rounded-lg bg-brand-500 px-5 text-sm font-medium text-white hover:bg-brand-600">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Nueva Venta (POS)
            </a>
        </div>
    </form>

    {{-- Table --}}
    <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 dark:border-gray-800">
                        <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Recibo</th>
                        <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Hora</th>
                        <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Cliente</th>
                        <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Sucursal</th>
                        <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Cajero</th>
                        <th class="px-5 py-3 text-right text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Total</th>
                        <th class="px-5 py-3 text-center text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Estado</th>
                        <th class="px-5 py-3 text-right text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($sales as $sale)
                        @php
                            $doc      = $sale->document;
                            $location = $sale->movements->first()?->fromLocation;
                            $status   = $doc?->status;
                        @endphp
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-white/[0.02]">
                            <td class="px-5 py-3 font-mono text-xs font-medium text-gray-800 dark:text-white/90">
                                {{ $doc?->doc_number ?? '—' }}
                            </td>
                            <td class="px-5 py-3 text-gray-500 dark:text-gray-400">
                                {{ $sale->created_at->format('H:i') }}
                                <span class="block text-xs text-gray-400">{{ $sale->created_at->format('d/m/Y') }}</span>
                            </td>
                            <td class="px-5 py-3 text-gray-700 dark:text-gray-300">
                                {{ $doc?->client_name ?: '—' }}
                                @if($doc?->client_nit)
                                    <span class="block text-xs text-gray-400">{{ $doc->client_nit }}</span>
                                @endif
                            </td>
                            <td class="px-5 py-3 text-gray-600 dark:text-gray-400">
                                {{ $location?->name ?? '—' }}
                            </td>
                            <td class="px-5 py-3 text-gray-600 dark:text-gray-400">
                                {{ $sale->user?->name ?? '—' }}
                            </td>
                            <td class="px-5 py-3 text-right font-medium text-gray-800 dark:text-white/90">
                                Bs {{ number_format($doc?->total_amount ?? 0, 2) }}
                            </td>
                            <td class="px-5 py-3 text-center">
                                @if($status?->value === 'open')
                                    <span class="inline-flex items-center rounded-full bg-success-50 px-2.5 py-0.5 text-xs font-medium text-success-700 dark:bg-success-500/10 dark:text-success-400">
                                        Abierto
                                    </span>
                                @elseif($status?->value === 'cancelled')
                                    <span class="inline-flex items-center rounded-full bg-error-50 px-2.5 py-0.5 text-xs font-medium text-error-600 dark:bg-error-500/10 dark:text-error-400">
                                        Cancelado
                                    </span>
                                @else
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-600 dark:bg-white/10 dark:text-gray-400">
                                        {{ $status?->value ?? '—' }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-5 py-3 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('sales.show', $sale->id) }}"
                                        class="rounded-lg bg-gray-100 px-3 py-1.5 text-xs font-medium text-gray-600 hover:bg-gray-200 dark:bg-white/10 dark:text-gray-400 dark:hover:bg-white/20">
                                        Ver
                                    </a>
                                    @if(auth()->user()->isAdmin() && $status?->value === 'open')
                                        <form method="POST" action="{{ route('sales.cancel', $sale->id) }}"
                                            onsubmit="return confirm('¿Cancelar venta {{ $doc?->doc_number }}? El stock se revertirá.')">
                                            @csrf
                                            <button type="submit"
                                                class="rounded-lg bg-error-50 px-3 py-1.5 text-xs font-medium text-error-600 hover:bg-error-100 dark:bg-error-500/10 dark:text-error-400 dark:hover:bg-error-500/20">
                                                Cancelar
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-5 py-12 text-center text-sm text-gray-400 dark:text-gray-500">
                                No hay ventas registradas en este período.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($sales->hasPages())
            <div class="border-t border-gray-100 px-5 py-4 dark:border-gray-800">
                {{ $sales->links() }}
            </div>
        @endif
    </div>
@endsection
