@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Traslados" />

    {{-- Flash messages --}}
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

    <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
        {{-- Header + filters --}}
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 px-6 py-4 dark:border-gray-800">
            <h3 class="text-base font-medium text-gray-800 dark:text-white/90">Registro de Traslados</h3>
            <div class="flex flex-wrap items-center gap-2">
                <form method="GET" action="{{ route('inventory.transfers.index') }}" class="flex flex-wrap items-center gap-2">
                    <input type="date" name="date_from" value="{{ request('date_from') }}"
                        class="shadow-theme-xs h-10 rounded-lg border border-gray-300 bg-transparent px-3 text-sm text-gray-800 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
                    <span class="text-xs text-gray-400">—</span>
                    <input type="date" name="date_to" value="{{ request('date_to') }}"
                        class="shadow-theme-xs h-10 rounded-lg border border-gray-300 bg-transparent px-3 text-sm text-gray-800 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />

                    {{-- Origen --}}
                    <div x-data="{ isOptionSelected: {{ request('from_location_id') ? 'true' : 'false' }} }" class="relative">
                        <select name="from_location_id"
                            class="shadow-theme-xs h-10 appearance-none rounded-lg border border-gray-300 bg-transparent px-3 pr-9 text-sm focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"
                            :class="isOptionSelected ? 'text-gray-800 dark:text-white/90' : 'text-gray-400 dark:text-gray-500'"
                            @change="isOptionSelected = true">
                            <option value="">Origen (todos)</option>
                            @foreach($locations as $loc)
                                <option value="{{ $loc->id }}" {{ request('from_location_id') == $loc->id ? 'selected' : '' }}
                                    class="dark:bg-gray-900 dark:text-gray-400">{{ $loc->name }}</option>
                            @endforeach
                        </select>
                        <span class="pointer-events-none absolute top-1/2 right-3 -translate-y-1/2 text-gray-500 dark:text-gray-400">
                            <svg class="stroke-current" width="16" height="16" viewBox="0 0 20 20" fill="none"><path d="M4.79175 7.396L10.0001 12.6043L15.2084 7.396" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </span>
                    </div>

                    {{-- Destino --}}
                    <div x-data="{ isOptionSelected: {{ request('to_location_id') ? 'true' : 'false' }} }" class="relative">
                        <select name="to_location_id"
                            class="shadow-theme-xs h-10 appearance-none rounded-lg border border-gray-300 bg-transparent px-3 pr-9 text-sm focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"
                            :class="isOptionSelected ? 'text-gray-800 dark:text-white/90' : 'text-gray-400 dark:text-gray-500'"
                            @change="isOptionSelected = true">
                            <option value="">Destino (todos)</option>
                            @foreach($locations as $loc)
                                <option value="{{ $loc->id }}" {{ request('to_location_id') == $loc->id ? 'selected' : '' }}
                                    class="dark:bg-gray-900 dark:text-gray-400">{{ $loc->name }}</option>
                            @endforeach
                        </select>
                        <span class="pointer-events-none absolute top-1/2 right-3 -translate-y-1/2 text-gray-500 dark:text-gray-400">
                            <svg class="stroke-current" width="16" height="16" viewBox="0 0 20 20" fill="none"><path d="M4.79175 7.396L10.0001 12.6043L15.2084 7.396" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </span>
                    </div>

                    <button type="submit" class="flex h-10 items-center rounded-lg bg-gray-100 px-3 text-sm font-medium text-gray-700 hover:bg-gray-200 dark:bg-white/10 dark:text-white/80 dark:hover:bg-white/20">
                        Filtrar
                    </button>
                    @if(request()->hasAny(['date_from', 'date_to', 'from_location_id', 'to_location_id']))
                        <a href="{{ route('inventory.transfers.index') }}" class="flex h-10 items-center rounded-lg px-3 text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                            Limpiar
                        </a>
                    @endif
                </form>
                <a href="{{ route('inventory.transfers.create') }}"
                   class="flex items-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600">
                    + Nuevo Traslado
                </a>
            </div>
        </div>

        {{-- Table --}}
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 dark:border-gray-800">
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Fecha</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Origen</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Destino</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Usuario</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Notas</th>
                        <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($transfers as $transfer)
                        @php
                            // Movements in a transfer come in pairs (out + in).
                            // "out" movements have from_location_id set; "in" have to_location_id set.
                            $outMove = $transfer->movements->first(fn($m) => $m->from_location_id !== null);
                            $inMove  = $transfer->movements->first(fn($m) => $m->to_location_id !== null);
                        @endphp
                        <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.02]">
                            <td class="px-6 py-3 text-gray-600 dark:text-gray-300">
                                {{ $transfer->created_at->format('d/m/Y H:i') }}
                            </td>
                            <td class="px-6 py-3 text-gray-700 dark:text-gray-300">
                                {{ $outMove?->fromLocation?->name ?? '—' }}
                            </td>
                            <td class="px-6 py-3 text-gray-700 dark:text-gray-300">
                                {{ $inMove?->toLocation?->name ?? '—' }}
                            </td>
                            <td class="px-6 py-3 text-gray-500 dark:text-gray-400">{{ $transfer->user?->name ?? '—' }}</td>
                            <td class="px-6 py-3 text-gray-500 dark:text-gray-400 max-w-xs truncate">
                                {{ $transfer->notes ?? '—' }}
                            </td>
                            <td class="px-6 py-3 text-right">
                                <a href="{{ route('inventory.transfers.show', $transfer->id) }}"
                                   class="rounded-lg bg-gray-100 px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-200 dark:bg-white/10 dark:text-white/80 dark:hover:bg-white/20">
                                    Ver
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-10 text-center text-sm text-gray-400 dark:text-gray-500">
                                No hay traslados registrados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($transfers->hasPages())
            <div class="border-t border-gray-100 px-6 py-4 dark:border-gray-800">
                {{ $transfers->links() }}
            </div>
        @endif
    </div>
@endsection
