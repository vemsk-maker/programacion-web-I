@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Traslados" />

    {{-- Flash messages --}}
    @if(session('success'))
        <div class="mb-6 flex items-center gap-3 rounded-2xl bg-emerald-50 border border-emerald-100 px-5 py-3 text-sm font-bold text-emerald-600">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 6L9 17l-5-5"/></svg>
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="mb-6 flex items-center gap-3 rounded-2xl bg-red-50 border border-red-100 px-5 py-3 text-sm font-bold text-[#e11d48]">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6L6 18M6 6l12 12"/></svg>
            {{ session('error') }}
        </div>
    @endif

    <div class="rounded-[2.5rem] border border-gray-100 bg-white shadow-sm overflow-hidden">
        {{-- Header + filters --}}
        <div class="border-b border-gray-50 px-8 py-6">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <h3 class="text-xl font-bold text-[#1e293b]">Registro de Traslados</h3>
                
                <div class="flex flex-wrap items-center gap-3">
                    <form method="GET" action="{{ route('inventory.transfers.index') }}" class="flex flex-wrap items-center gap-3">
                        <div class="flex items-center gap-2">
                            <input type="date" name="date_from" value="{{ request('date_from') }}"
                                class="h-10 rounded-xl border border-gray-200 bg-gray-50 px-3 text-sm font-semibold text-[#1e293b] focus:border-[#e11d48] focus:outline-none transition-all" />
                            <span class="text-xs font-bold text-gray-300">—</span>
                            <input type="date" name="date_to" value="{{ request('date_to') }}"
                                class="h-10 rounded-xl border border-gray-200 bg-gray-50 px-3 text-sm font-semibold text-[#1e293b] focus:border-[#e11d48] focus:outline-none transition-all" />
                        </div>

                        {{-- Origen --}}
                        <div x-data="{ isOptionSelected: {{ request('from_location_id') ? 'true' : 'false' }} }" class="relative">
                            <select name="from_location_id"
                                class="h-10 appearance-none rounded-xl border border-gray-200 bg-gray-50 px-4 pr-10 text-sm font-semibold focus:border-[#e11d48] focus:outline-none transition-all"
                                :class="isOptionSelected ? 'text-[#1e293b]' : 'text-gray-400'"
                                @change="isOptionSelected = true">
                                <option value="">Origen (todos)</option>
                                @foreach($locations as $loc)
                                    <option value="{{ $loc->id }}" {{ request('from_location_id') == $loc->id ? 'selected' : '' }}>{{ $loc->name }}</option>
                                @endforeach
                            </select>
                            <span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-gray-400">
                                <svg width="16" height="16" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M4.79175 7.396L10.0001 12.6043L15.2084 7.396" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            </span>
                        </div>

                        {{-- Destino --}}
                        <div x-data="{ isOptionSelected: {{ request('to_location_id') ? 'true' : 'false' }} }" class="relative">
                            <select name="to_location_id"
                                class="h-10 appearance-none rounded-xl border border-gray-200 bg-gray-50 px-4 pr-10 text-sm font-semibold focus:border-[#e11d48] focus:outline-none transition-all"
                                :class="isOptionSelected ? 'text-[#1e293b]' : 'text-gray-400'"
                                @change="isOptionSelected = true">
                                <option value="">Destino (todos)</option>
                                @foreach($locations as $loc)
                                    <option value="{{ $loc->id }}" {{ request('to_location_id') == $loc->id ? 'selected' : '' }}>{{ $loc->name }}</option>
                                @endforeach
                            </select>
                            <span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-gray-400">
                                <svg width="16" height="16" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M4.79175 7.396L10.0001 12.6043L15.2084 7.396" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            </span>
                        </div>

                        <button type="submit" class="h-10 rounded-xl bg-[#1e293b] px-5 text-sm font-bold text-white hover:bg-[#334155] transition-all">
                            Filtrar
                        </button>
                        
                        @if(request()->hasAny(['date_from', 'date_to', 'from_location_id', 'to_location_id']))
                            <a href="{{ route('inventory.transfers.index') }}" class="flex h-10 items-center px-2 text-sm font-bold text-gray-400 hover:text-[#e11d48] transition-colors">
                                Limpiar
                            </a>
                        @endif
                    </form>

                    <a href="{{ route('inventory.transfers.create') }}"
                       class="flex h-10 items-center gap-2 rounded-xl bg-[#e11d48] px-5 text-sm font-black uppercase tracking-widest text-white shadow-md hover:bg-[#be123c] transition-all active:scale-95">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M12 5v14M5 12h14"/></svg>
                        Nuevo Traslado
                    </a>
                </div>
            </div>
        </div>

        {{-- Table --}}
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50/50">
                        <th class="px-8 py-4 text-left text-[10px] font-black uppercase tracking-widest text-gray-400">Fecha</th>
                        <th class="px-6 py-4 text-left text-[10px] font-black uppercase tracking-widest text-gray-400">Origen</th>
                        <th class="px-6 py-4 text-left text-[10px] font-black uppercase tracking-widest text-gray-400">Destino</th>
                        <th class="px-6 py-4 text-left text-[10px] font-black uppercase tracking-widest text-gray-400">Usuario</th>
                        <th class="px-6 py-4 text-left text-[10px] font-black uppercase tracking-widest text-gray-400">Notas</th>
                        <th class="px-8 py-4 text-right text-[10px] font-black uppercase tracking-widest text-gray-400">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($transfers as $transfer)
                        @php
                            $outMove = $transfer->movements->first(fn($m) => $m->from_location_id !== null);
                            $inMove  = $transfer->movements->first(fn($m) => $m->to_location_id !== null);
                        @endphp
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-8 py-4 font-bold text-[#1e293b]">
                                {{ $transfer->created_at->format('d/m/Y') }}
                                <span class="block text-[10px] font-medium text-gray-400">{{ $transfer->created_at->format('H:i') }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="rounded-lg bg-blue-50 px-2 py-1 text-xs font-bold text-blue-600">
                                    {{ $outMove?->fromLocation?->name ?? '—' }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="rounded-lg bg-emerald-50 px-2 py-1 text-xs font-bold text-emerald-600">
                                    {{ $inMove?->toLocation?->name ?? '—' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 font-medium text-gray-500">{{ $transfer->user?->name ?? '—' }}</td>
                            <td class="px-6 py-4 text-gray-500 max-w-xs truncate italic">
                                {{ $transfer->notes ?? '—' }}
                            </td>
                            <td class="px-8 py-4 text-right">
                                <a href="{{ route('inventory.transfers.show', $transfer->id) }}"
                                    class="inline-flex h-8 items-center rounded-lg bg-gray-100 px-4 text-xs font-black uppercase tracking-widest text-gray-500 hover:bg-[#1e293b] hover:text-white transition-all">
                                    Ver
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-8 py-12 text-center">
                                <div class="flex flex-col items-center">
                                    <div class="mb-2 text-gray-200">
                                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M21 12a9 9 0 11-18 0 9 9 0 0118 0zM12 9v2m0 4h.01"/></svg>
                                    </div>
                                    <span class="text-sm font-bold text-gray-400">No hay traslados registrados con esos filtros.</span>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($transfers->hasPages())
            <div class="border-t border-gray-50 px-8 py-6">
                {{ $transfers->links() }}
            </div>
        @endif
    </div>
@endsection