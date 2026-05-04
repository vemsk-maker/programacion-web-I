@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Vencimientos de Lotes" />

    {{-- ── Filtros ── --}}
    <div class="rounded-[2.5rem] border border-gray-100 bg-white p-8 shadow-sm">
        <div class="mb-6 border-b border-gray-50 pb-6">
            <h3 class="text-2xl font-bold text-[#1e293b]">Vencimientos de Lotes</h3>
            <p class="text-sm text-gray-500">Alertas de productos próximos a vencer o ya vencidos</p>
        </div>

        <form method="GET" action="{{ route('reports.expirations') }}"
              class="flex flex-wrap items-end gap-3">

            {{-- Días preset --}}
            <div>
                <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-widest text-gray-400">Vencen en</label>
                <div class="relative">
                    <select name="days"
                        class="h-11 w-40 appearance-none rounded-xl border border-gray-200 bg-white px-4 pr-10 text-sm text-gray-700 focus:border-gray-400 focus:outline-none transition-all">
                        <option value="0"  {{ $days == 0  ? 'selected' : '' }}>Solo vencidos</option>
                        <option value="15" {{ $days == 15 ? 'selected' : '' }}>15 días</option>
                        <option value="30" {{ $days == 30 ? 'selected' : '' }}>30 días</option>
                        <option value="60" {{ $days == 60 ? 'selected' : '' }}>60 días</option>
                        <option value="90" {{ $days == 90 ? 'selected' : '' }}>90 días</option>
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
                        <option value="">Todas las ubicaciones</option>
                        @foreach($locations as $loc)
                            <option value="{{ $loc->id }}" {{ $locationId == $loc->id ? 'selected' : '' }}>{{ $loc->name }}</option>
                        @endforeach
                    </select>
                    <span class="pointer-events-none absolute top-1/2 right-4 -translate-y-1/2 text-gray-400">
                        <svg width="16" height="16" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M4.79175 7.396L10.0001 12.6043L15.2084 7.396" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </span>
                </div>
            </div>

            <button type="submit" class="h-11 rounded-xl bg-[#1e293b] px-6 text-sm font-bold text-white hover:bg-[#334155] transition-all">
                Filtrar
            </button>
            <a href="{{ route('reports.expirations') }}" class="text-sm font-bold text-gray-400 hover:text-[#e11d48] transition-colors">
                Limpiar
            </a>

            <a href="{{ route('reports.expirations', array_merge(request()->query(), ['export' => 1])) }}"
                class="ml-auto flex h-11 items-center gap-2 rounded-xl border border-gray-200 bg-white px-5 text-sm font-bold text-gray-500 hover:bg-gray-50 transition-all">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                Exportar CSV
            </a>
        </form>
    </div>

    {{-- Leyenda de urgencia --}}
    <div class="my-5 flex flex-wrap items-center gap-3">
        <span class="text-[10px] font-bold uppercase tracking-widest text-gray-400">Urgencia:</span>
        <span class="inline-flex items-center gap-1.5 rounded-lg bg-red-50 px-3 py-1 text-xs font-bold text-[#e11d48]">
            <span class="h-1.5 w-1.5 rounded-full bg-[#e11d48]"></span>Vencido
        </span>
        <span class="inline-flex items-center gap-1.5 rounded-lg bg-orange-50 px-3 py-1 text-xs font-bold text-orange-600">
            <span class="h-1.5 w-1.5 rounded-full bg-orange-500"></span>≤ 15 días
        </span>
        <span class="inline-flex items-center gap-1.5 rounded-lg bg-amber-50 px-3 py-1 text-xs font-bold text-amber-600">
            <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>≤ 30 días
        </span>
        <span class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-600">
            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>≤ 60 días
        </span>
    </div>

    {{-- Tabla --}}
    <div class="rounded-[2.5rem] border border-gray-100 bg-white shadow-sm">

        <div class="border-b border-gray-50 px-8 py-5">
            <p class="text-sm font-bold text-[#1e293b]">
                {{ $batches->count() }} {{ Str::plural('lote', $batches->count()) }} encontrado{{ $batches->count() !== 1 ? 's' : '' }}
                @if($days > 0)
                    que vencen dentro de <span class="text-[#e11d48]">{{ $days }} días</span>
                @else
                    <span class="text-[#e11d48]">ya vencidos</span>
                @endif
            </p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="text-[10px] font-bold uppercase tracking-widest text-gray-400">
                        <th class="pb-4 pl-8 pt-6">Urgencia</th>
                        <th class="pb-4 pt-6">Lote</th>
                        <th class="pb-4 pt-6">Producto</th>
                        <th class="pb-4 pt-6">Ubicación</th>
                        <th class="pb-4 pt-6 text-center">Vencimiento</th>
                        <th class="pb-4 pr-8 pt-6 text-right">Stock</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 border-t border-gray-50">
                    @forelse($batches as $row)
                        @php
                            $exp  = \Carbon\Carbon::parse($row->expiration_date);
                            $diff = now()->startOfDay()->diffInDays($exp->startOfDay(), false);
                            if ($diff < 0) {
                                $badge = 'bg-red-50 text-[#e11d48]';
                                $label = 'Vencido';
                            } elseif ($diff <= 15) {
                                $badge = 'bg-orange-50 text-orange-600';
                                $label = "En {$diff}d";
                            } elseif ($diff <= 30) {
                                $badge = 'bg-amber-50 text-amber-600';
                                $label = "En {$diff}d";
                            } else {
                                $badge = 'bg-emerald-50 text-emerald-600';
                                $label = "En {$diff}d";
                            }
                        @endphp
                        <tr class="group transition-colors hover:bg-gray-50/50">
                            <td class="py-5 pl-8">
                                <span class="inline-flex items-center rounded-lg px-2.5 py-1 text-[10px] font-black uppercase {{ $badge }}">
                                    {{ $label }}
                                </span>
                            </td>
                            <td class="py-5 font-mono text-xs font-bold text-gray-400">
                                {{ $row->batch_code }}
                            </td>
                            <td class="py-5 text-sm font-bold text-[#1e293b]">
                                {{ $row->product }}
                            </td>
                            <td class="py-5">
                                <span class="inline-flex items-center rounded-lg bg-gray-100 px-3 py-1 text-[10px] font-bold uppercase text-gray-500">
                                    {{ $row->location }}
                                </span>
                            </td>
                            <td class="py-5 text-center font-mono text-sm font-bold {{ $diff < 0 ? 'text-[#e11d48]' : 'text-gray-500' }}">
                                {{ $exp->format('d/m/Y') }}
                            </td>
                            <td class="py-5 pr-8 text-right font-black text-[#1e293b]">
                                {{ number_format($row->quantity, 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-20 text-center">
                                <p class="text-sm font-medium italic text-gray-400">
                                    @if($days == 0)
                                        No hay lotes vencidos con stock. ✓
                                    @else
                                        No hay lotes que vencen en los próximos {{ $days }} días.
                                    @endif
                                </p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-8">
        <a href="{{ route('reports.index') }}"
           class="inline-flex items-center gap-2 rounded-2xl bg-gray-100 px-8 py-3 text-sm font-bold text-gray-500 transition-all hover:bg-gray-200 hover:text-[#1e293b]">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
            Volver a Reportes
        </a>
    </div>
@endsection