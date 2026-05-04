@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Ajustes de Inventario" />

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
                <div>
                    <h3 class="text-xl font-bold text-[#1e293b]">Ajustes de Inventario</h3>
                    <p class="text-sm text-gray-400 font-medium mt-0.5">Correcciones manuales de stock</p>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <form method="GET" action="{{ route('inventory.adjustments.index') }}" class="flex flex-wrap items-center gap-3">
                        <div class="flex items-center gap-2">
                            <input type="date" name="date_from" value="{{ request('date_from') }}"
                                class="h-10 rounded-xl border border-gray-200 bg-gray-50 px-3 text-sm font-semibold text-[#1e293b] focus:border-[#e11d48] focus:outline-none transition-all" />
                            <span class="text-xs font-bold text-gray-300">—</span>
                            <input type="date" name="date_to" value="{{ request('date_to') }}"
                                class="h-10 rounded-xl border border-gray-200 bg-gray-50 px-3 text-sm font-semibold text-[#1e293b] focus:border-[#e11d48] focus:outline-none transition-all" />
                        </div>

                        <div x-data="{ isOptionSelected: {{ request('location_id') ? 'true' : 'false' }} }" class="relative">
                            <select name="location_id"
                                class="h-10 appearance-none rounded-xl border border-gray-200 bg-gray-50 px-4 pr-10 text-sm font-semibold focus:border-[#e11d48] focus:outline-none transition-all"
                                :class="isOptionSelected ? 'text-[#1e293b]' : 'text-gray-400'"
                                @change="isOptionSelected = true">
                                <option value="">Ubicación (todas)</option>
                                @foreach($locations as $loc)
                                    <option value="{{ $loc->id }}" {{ request('location_id') == $loc->id ? 'selected' : '' }}>{{ $loc->name }}</option>
                                @endforeach
                            </select>
                            <span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-gray-400">
                                <svg width="16" height="16" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M4.79175 7.396L10.0001 12.6043L15.2084 7.396" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            </span>
                        </div>

                        <button type="submit" class="h-10 rounded-xl bg-[#1e293b] px-5 text-sm font-bold text-white hover:bg-[#334155] transition-all">
                            Filtrar
                        </button>

                        @if(request()->hasAny(['date_from', 'date_to', 'location_id']))
                            <a href="{{ route('inventory.adjustments.index') }}" class="flex h-10 items-center px-2 text-sm font-bold text-gray-400 hover:text-[#e11d48] transition-colors">
                                Limpiar
                            </a>
                        @endif
                    </form>

                    <a href="{{ route('inventory.adjustments.create') }}"
                       class="flex h-10 items-center gap-2 rounded-xl bg-[#e11d48] px-5 text-sm font-black uppercase tracking-widest text-white shadow-md hover:bg-[#be123c] transition-all active:scale-95">
                        + Nuevo Ajuste
                    </a>
                </div>
            </div>
        </div>

        {{-- Tabla --}}
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-gray-50">
                        <th class="px-8 py-4 text-[11px] font-black uppercase tracking-wider text-gray-400 bg-gray-50/50">#</th>
                        <th class="px-6 py-4 text-[11px] font-black uppercase tracking-wider text-gray-400 bg-gray-50/50">Fecha</th>
                        <th class="px-6 py-4 text-[11px] font-black uppercase tracking-wider text-gray-400 bg-gray-50/50">Ubicación</th>
                        <th class="px-6 py-4 text-[11px] font-black uppercase tracking-wider text-gray-400 bg-gray-50/50">Productos</th>
                        <th class="px-6 py-4 text-[11px] font-black uppercase tracking-wider text-gray-400 bg-gray-50/50">Registrado por</th>
                        <th class="px-6 py-4 text-[11px] font-black uppercase tracking-wider text-gray-400 bg-gray-50/50">Notas</th>
                        <th class="px-6 py-4 text-[11px] font-black uppercase tracking-wider text-gray-400 bg-gray-50/50 text-center">Detalle</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse ($adjustments as $adj)
                        <tr class="hover:bg-gray-50/40 transition-colors">
                            <td class="px-8 py-4 text-sm font-black text-gray-400">#{{ $adj->id }}</td>
                            <td class="px-6 py-4 text-sm font-semibold text-[#1e293b]">
                                {{ $adj->created_at->format('d/m/Y H:i') }}
                            </td>
                            <td class="px-6 py-4 text-sm font-semibold text-gray-600">
                                {{ $adj->originLocation?->name ?? '—' }}
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-wrap gap-1">
                                    @foreach ($adj->movements as $mov)
                                        @php
                                            $isPositive = ! is_null($mov->to_location_id);
                                            $sign = $isPositive ? '+' : '-';
                                            $color = $isPositive ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-700';
                                        @endphp
                                        <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-xs font-bold {{ $color }}">
                                            {{ $sign }}{{ $mov->quantity }} {{ $mov->product->name ?? '?' }}
                                        </span>
                                    @endforeach
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ $adj->user?->name ?? '—' }}</td>
                            <td class="px-6 py-4 text-sm text-gray-400 max-w-xs truncate">{{ $adj->notes ?? '—' }}</td>
                            <td class="px-6 py-4 text-center">
                                <a href="{{ route('inventory.adjustments.show', $adj->id) }}"
                                   class="rounded-lg bg-blue-50 px-3 py-1.5 text-xs font-black text-blue-700 hover:bg-blue-100 transition-colors">
                                    Ver
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-8 py-16 text-center">
                                <p class="text-sm font-bold text-gray-400">No hay ajustes registrados.</p>
                                <a href="{{ route('inventory.adjustments.create') }}" class="mt-2 inline-block text-sm font-black text-[#e11d48] hover:underline">
                                    Registrar el primer ajuste
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Paginación --}}
        @if ($adjustments->hasPages())
            <div class="px-8 py-4 border-t border-gray-50">
                {{ $adjustments->links() }}
            </div>
        @endif
    </div>
@endsection
