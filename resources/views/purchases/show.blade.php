@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Detalle del Traslado" />

    {{-- Flash --}}
    @if(session('success'))
        <div class="mb-6 flex items-center gap-3 rounded-2xl bg-emerald-50 border border-emerald-100 px-5 py-3 text-sm font-bold text-emerald-700">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
            {{ session('success') }}
        </div>
    @endif

    @php
        $outMove         = $group->movements->first(fn ($m) => $m->from_location_id !== null);
        $inMove          = $group->movements->first(fn ($m) => $m->to_location_id !== null);
        $uniqueMovements = $group->movements->where('from_location_id', '!=', null)->values();
    @endphp

    {{-- ── Tarjetas de cabecera ── --}}
    <div class="mb-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">

        {{-- Fecha --}}
        <div class="rounded-[2rem] border border-gray-100 bg-white p-6 shadow-sm">
            <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400">Fecha del Traslado</p>
            <p class="mt-1 text-xl font-black text-[#1e293b]">
                {{ $group->created_at->format('d/m/Y') }}
            </p>
            <p class="text-xs font-bold text-[#e11d48]">{{ $group->created_at->format('H:i') }}</p>
        </div>

        {{-- Origen --}}
        <div class="rounded-[2rem] border border-gray-100 bg-white p-6 shadow-sm">
            <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400">Origen</p>
            <p class="mt-1 text-lg font-bold text-[#1e293b]">
                {{ $outMove?->fromLocation?->name ?? '—' }}
            </p>
            <span class="mt-2 inline-flex items-center rounded-lg bg-blue-50 px-2.5 py-1 text-[10px] font-black uppercase text-blue-600">
                Salida
            </span>
        </div>

        {{-- Destino --}}
        <div class="rounded-[2rem] border border-gray-100 bg-white p-6 shadow-sm">
            <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400">Destino</p>
            <p class="mt-1 text-lg font-bold text-[#1e293b]">
                {{ $inMove?->toLocation?->name ?? '—' }}
            </p>
            <span class="mt-2 inline-flex items-center rounded-lg bg-emerald-50 px-2.5 py-1 text-[10px] font-black uppercase text-emerald-600">
                Entrada
            </span>
        </div>

        {{-- Registrado por --}}
        <div class="rounded-[2rem] border border-gray-100 bg-white p-6 shadow-sm">
            <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400">Registrado por</p>
            <p class="mt-1 text-lg font-bold text-[#1e293b]">
                {{ $group->user?->name ?? '—' }}
            </p>
            <p class="text-xs font-medium text-gray-400">{{ $group->created_at->format('H:i') }}</p>
        </div>
    </div>

    {{-- ── Tabla de productos ── --}}
    <div class="rounded-[2.5rem] border border-gray-100 bg-white shadow-sm">

        <div class="flex items-center justify-between border-b border-gray-50 px-8 py-6">
            <div>
                <h3 class="text-xl font-bold text-[#1e293b]">Productos trasladados</h3>
                <p class="text-sm text-gray-500">Detalle de ítems movidos en este traslado</p>
            </div>
            <button type="button" onclick="window.print()"
                class="flex h-10 items-center gap-2 rounded-xl bg-[#1e293b] px-5 text-sm font-bold text-white hover:bg-[#334155] transition-all active:scale-95 print:hidden">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2m-12 0v5h12v-5"/></svg>
                Imprimir nota
            </button>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="text-[10px] font-bold uppercase tracking-widest text-gray-400">
                        <th class="pb-4 pl-8 pt-6">Producto</th>
                        <th class="pb-4 pt-6">Lote</th>
                        <th class="pb-4 pt-6">Vencimiento</th>
                        <th class="pb-4 pr-8 pt-6 text-right">Cantidad</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 border-t border-gray-50">
                    @foreach($uniqueMovements as $movement)
                        <tr class="group transition-colors hover:bg-gray-50/50">
                            <td class="py-5 pl-8 text-sm font-bold text-[#1e293b]">
                                {{ $movement->product->name }}
                            </td>
                            <td class="py-5">
                                <span class="font-mono text-xs font-bold text-gray-400">
                                    {{ $movement->batch?->batch_code ?? '—' }}
                                </span>
                            </td>
                            <td class="py-5">
                                @if($movement->batch?->expiration_date)
                                    @php $exp = $movement->batch->expiration_date; @endphp
                                    <span class="text-sm font-bold {{ $exp->isPast() ? 'text-[#e11d48]' : ($exp->diffInDays() < 30 ? 'text-amber-500' : 'text-gray-500') }}">
                                        {{ $exp->format('d/m/Y') }}
                                    </span>
                                @else
                                    <span class="text-gray-300">—</span>
                                @endif
                            </td>
                            <td class="py-5 pr-8 text-right font-black text-[#e11d48] text-base">
                                {{ number_format($movement->quantity, 2) }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Footer con conteo --}}
        <div class="flex items-center justify-end gap-4 rounded-b-[2.5rem] border-t border-gray-50 bg-gray-50/30 px-8 py-5">
            <span class="text-[10px] font-bold uppercase tracking-widest text-gray-400">Total de ítems:</span>
            <span class="font-mono text-2xl font-black text-[#1e293b]">
                {{ $uniqueMovements->count() }}
            </span>
        </div>
    </div>

    {{-- Notas --}}
    @if($group->notes)
        <div class="mt-6 rounded-[2rem] border border-gray-100 bg-white px-8 py-5 shadow-sm">
            <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400">Observaciones</p>
            <p class="mt-1.5 text-sm font-medium italic text-gray-600">"{{ $group->notes }}"</p>
        </div>
    @endif

    {{-- Print-only header --}}
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

    {{-- Acciones --}}
    <div class="mt-8 print:hidden">
        <a href="{{ route('inventory.transfers.index') }}"
           class="inline-flex items-center gap-2 rounded-2xl bg-gray-100 px-8 py-3 text-sm font-bold text-gray-500 transition-all hover:bg-gray-200 hover:text-[#1e293b]">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
            Volver a Traslados
        </a>
    </div>
@endsection