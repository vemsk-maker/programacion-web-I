@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Detalle del Traslado" />

    {{-- Notificaciones de Éxito --}}
    @if(session('success'))
        <div class="mb-6 flex items-center gap-3 rounded-2xl bg-emerald-50 border border-emerald-100 px-5 py-3 text-sm font-bold text-emerald-600">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 6L9 17l-5-5"/></svg>
            {{ session('success') }}
        </div>
    @endif

    @php
        // Lógica de movimientos (sin cambios)
        $outMove = $group->movements->first(fn ($m) => $m->from_location_id !== null);
        $inMove  = $group->movements->first(fn ($m) => $m->to_location_id !== null);
        $uniqueMovements = $group->movements
            ->where('from_location_id', '!=', null)
            ->values();
    @endphp

    {{-- ── Cabecera de Información (Tarjetas Ayma Style) ── --}}
    <div class="mb-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
        {{-- Fecha --}}
        <div class="rounded-[2rem] border border-gray-100 bg-white p-6 shadow-sm">
            <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400">Fecha del Traslado</p>
            <p class="mt-1 text-base font-bold text-[#1e293b]">
                {{ $group->created_at->format('d/m/Y') }}
            </p>
            <p class="text-xs font-medium text-[#e11d48]">{{ $group->created_at->format('H:i') }}</p>
        </div>

        {{-- Origen --}}
        <div class="rounded-[2rem] border border-gray-100 bg-white p-6 shadow-sm">
            <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400">Ubicación Origen</p>
            <p class="mt-1 text-base font-bold text-[#1e293b]">
                {{ $outMove?->fromLocation?->name ?? '—' }}
            </p>
            <span class="mt-2 inline-flex items-center rounded-lg bg-blue-50 px-2 py-0.5 text-[10px] font-black uppercase text-blue-600">Salida</span>
        </div>

        {{-- Destination --}}
        <div class="rounded-[2rem] border border-gray-100 bg-white p-6 shadow-sm">
            <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400">Ubicación Destino</p>
            <p class="mt-1 text-base font-bold text-[#1e293b]">
                {{ $inMove?->toLocation?->name ?? '—' }}
            </p>
            <span class="mt-2 inline-flex items-center rounded-lg bg-emerald-50 px-2 py-0.5 text-[10px] font-black uppercase text-emerald-600">Entrada</span>
        </div>

        {{-- User --}}
        <div class="rounded-[2rem] border border-gray-100 bg-white p-6 shadow-sm">
            <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400">Registrado por</p>
            <p class="mt-1 text-base font-bold text-[#1e293b]">
                {{ $group->user?->name ?? '—' }}
            </p>
        </div>
    </div>

    {{-- ── Tabla de Productos (Estilo Redondeado Ayma) ── --}}
    <div class="overflow-hidden rounded-[2.5rem] border border-gray-100 bg-white shadow-sm">
        <div class="flex items-center justify-between border-b border-gray-50 bg-gray-50/50 px-8 py-5">
            <h3 class="text-sm font-black uppercase tracking-widest text-[#1e293b]">Productos trasladados</h3>
            <button type="button" onclick="window.print()"
                class="inline-flex items-center gap-2 rounded-xl bg-[#1e293b] px-4 py-2 text-xs font-bold text-white transition-all hover:bg-[#334155] active:scale-95 print:hidden">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2m-12 0v5h12v-5"/></svg>
                Imprimir nota
            </button>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-white">
                        <th class="px-8 py-4 text-left text-[10px] font-black uppercase tracking-widest text-gray-400">Producto</th>
                        <th class="px-6 py-4 text-left text-[10px] font-black uppercase tracking-widest text-gray-400">Lote</th>
                        <th class="px-6 py-4 text-left text-[10px] font-black uppercase tracking-widest text-gray-400">Vencimiento</th>
                        <th class="px-8 py-4 text-right text-[10px] font-black uppercase tracking-widest text-gray-400">Cantidad</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($uniqueMovements as $movement)
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-8 py-4 font-bold text-[#1e293b]">
                                {{ $movement->product->name }}
                            </td>
                            <td class="px-6 py-4 font-mono text-xs font-bold text-gray-500">
                                {{ $movement->batch?->batch_code ?? '—' }}
                            </td>
                            <td class="px-6 py-4">
                                @if($movement->batch?->expiration_date)
                                    @php $exp = $movement->batch->expiration_date; @endphp
                                    <span class="font-bold {{ $exp->isPast() ? 'text-[#e11d48]' : ($exp->diffInDays() < 30 ? 'text-amber-600' : 'text-gray-500') }}">
                                        {{ $exp->format('d/m/Y') }}
                                    </span>
                                @else
                                    <span class="text-gray-300">—</span>
                                @endif
                            </td>
                            <td class="px-8 py-4 text-right font-black text-[#e11d48] text-base">
                                {{ number_format($movement->quantity, 2) }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Notas --}}
    @if($group->notes)
        <div class="mt-6 rounded-[2rem] border border-gray-100 bg-white px-8 py-5 shadow-sm">
            <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400">Observaciones</p>
            <p class="mt-1 text-sm font-medium text-gray-600 italic">"{{ $group->notes }}"</p>
        </div>
    @endif

    {{-- Print-only header (hidden on screen) --}}
    <div class="hidden print:block print:mb-8 print:border-b-2 print:border-black print:pb-4">
        <h1 class="text-2xl font-black uppercase tracking-tighter text-black">Micromercado Ayma</h1>
        <h2 class="text-lg font-bold text-gray-700">Nota de Traslado de Inventario</h2>
        <div class="mt-4 grid grid-cols-2 text-sm">
            <p><strong>Origen:</strong> {{ $outMove?->fromLocation?->name }}</p>
            <p><strong>Destino:</strong> {{ $inMove?->toLocation?->name }}</p>
            <p><strong>Fecha:</strong> {{ $group->created_at->format('d/m/Y H:i') }}</p>
            <p><strong>Responsable:</strong> {{ $group->user?->name }}</p>
        </div>
    </div>

    {{-- Acciones Finales --}}
    <div class="mt-8 flex items-center gap-4 print:hidden">
        <a href="{{ route('inventory.transfers.index') }}"
           class="inline-flex items-center gap-2 rounded-2xl bg-gray-100 px-8 py-3 text-sm font-black uppercase tracking-widest text-gray-500 transition-all hover:bg-gray-200 hover:text-[#1e293b]">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
            Volver a Traslados
        </a>
    </div>
@endsection