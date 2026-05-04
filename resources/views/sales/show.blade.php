@extends('layouts.app')

@section('content')
    @php
        $doc      = $group->document;
        $location = $group->movements->first()?->fromLocation;
        $isOpen   = $doc?->status?->value === 'open';
    @endphp

    {{-- Print header (oculto en pantalla) --}}
    <div class="hidden print:block print:mb-6">
        <div class="print:flex print:items-start print:justify-between">
            <div>
                <h1 class="text-2xl font-bold">RECIBO DE VENTA</h1>
                <p class="text-sm">{{ $location?->name }}</p>
            </div>
            <div class="text-right text-sm">
                <p class="text-xl font-bold font-mono">{{ $doc?->doc_number }}</p>
                <p>{{ $group->created_at->format('d/m/Y H:i') }}</p>
            </div>
        </div>
        @if($doc?->client_name || $doc?->client_nit)
            <div class="mt-3 border-t pt-3 text-sm">
                @if($doc->client_name) <p>Cliente: <strong>{{ $doc->client_name }}</strong></p> @endif
                @if($doc->client_nit)  <p>NIT/CI: {{ $doc->client_nit }}</p> @endif
            </div>
        @endif
        <div class="mt-2 border-t border-b py-1 text-xs text-gray-500">
            Cajero: {{ $group->user?->name }}
        </div>
    </div>

    <x-common.page-breadcrumb pageTitle="Detalle de Venta" />

    {{-- Alerts --}}
    @if(session('success'))
        <div class="mb-6 flex items-center gap-3 rounded-2xl bg-emerald-50 border border-emerald-100 px-5 py-3 text-sm font-bold text-emerald-700 print:hidden">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="mb-6 flex items-center gap-3 rounded-2xl bg-red-50 border border-red-100 px-5 py-3 text-sm font-bold text-[#e11d48] print:hidden">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6L6 18M6 6l12 12"/></svg>
            {{ session('error') }}
        </div>
    @endif

    {{-- ── Tarjetas de cabecera ── --}}
    <div class="mb-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4 print:hidden">

        {{-- N° Recibo --}}
        <div class="rounded-[2rem] border border-gray-100 bg-white p-6 shadow-sm">
            <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400">N° Recibo</p>
            <p class="mt-1 font-mono text-xl font-black text-[#1e293b]">
                {{ $doc?->doc_number ?? '—' }}
            </p>
            <div class="mt-2">
                @if($doc?->status?->value === 'open')
                    <span class="inline-flex items-center rounded-lg bg-emerald-50 px-2.5 py-1 text-[10px] font-black uppercase text-emerald-600">
                        Abierto
                    </span>
                @elseif($doc?->status?->value === 'cancelled')
                    <span class="inline-flex items-center rounded-lg bg-red-50 px-2.5 py-1 text-[10px] font-black uppercase text-[#e11d48]">
                        Cancelado
                    </span>
                @endif
            </div>
        </div>

        {{-- Fecha --}}
        <div class="rounded-[2rem] border border-gray-100 bg-white p-6 shadow-sm">
            <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400">Fecha y Hora</p>
            <p class="mt-1 text-xl font-black text-[#1e293b]">
                {{ $group->created_at->format('d/m/Y') }}
            </p>
            <p class="text-xs font-bold text-[#e11d48]">{{ $group->created_at->format('H:i') }}</p>
        </div>

        {{-- Sucursal --}}
        <div class="rounded-[2rem] border border-gray-100 bg-white p-6 shadow-sm">
            <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400">Sucursal</p>
            <p class="mt-1 text-lg font-bold text-[#1e293b]">
                @if($location?->name)
                    <span class="inline-flex items-center rounded-lg bg-gray-100 px-3 py-1 text-sm font-bold text-gray-500 uppercase">
                        {{ $location->name }}
                    </span>
                @else
                    —
                @endif
            </p>
        </div>

        {{-- Cajero --}}
        <div class="rounded-[2rem] border border-gray-100 bg-white p-6 shadow-sm">
            <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400">Cajero</p>
            <p class="mt-1 text-lg font-bold text-[#1e293b]">
                {{ $group->user?->name ?? '—' }}
            </p>
        </div>
    </div>

    {{-- Datos del cliente (si existen) --}}
    @if($doc?->client_name || $doc?->client_nit)
        <div class="mb-6 rounded-[2rem] border border-gray-100 bg-white px-8 py-5 shadow-sm print:hidden">
            <p class="mb-3 text-[10px] font-bold uppercase tracking-widest text-gray-400">Datos del Cliente</p>
            <div class="flex flex-wrap gap-6 text-sm">
                @if($doc->client_name)
                    <div>
                        <span class="text-gray-400">Nombre: </span>
                        <span class="font-bold text-[#1e293b]">{{ $doc->client_name }}</span>
                    </div>
                @endif
                @if($doc->client_nit)
                    <div>
                        <span class="text-gray-400">NIT/CI: </span>
                        <span class="font-bold text-[#1e293b]">{{ $doc->client_nit }}</span>
                    </div>
                @endif
            </div>
        </div>
    @endif

    {{-- ── Tabla de productos ── --}}
    <div class="rounded-[2.5rem] border border-gray-100 bg-white shadow-sm">

        <div class="flex items-center justify-between border-b border-gray-50 px-8 py-6 print:hidden">
            <div>
                <h3 class="text-xl font-bold text-[#1e293b]">Productos vendidos</h3>
                <p class="text-sm text-gray-500">Detalle de ítems de esta venta</p>
            </div>
            <button type="button" onclick="window.print()"
                class="flex h-10 items-center gap-2 rounded-xl bg-[#1e293b] px-5 text-sm font-bold text-white hover:bg-[#334155] transition-all active:scale-95">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2m-12 0v5h12v-5"/></svg>
                Imprimir recibo
            </button>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="text-[10px] font-bold uppercase tracking-widest text-gray-400">
                        <th class="pb-4 pl-8 pt-6">Producto</th>
                        <th class="pb-4 pt-6">Lote</th>
                        <th class="pb-4 pt-6 text-right">Cant.</th>
                        <th class="pb-4 pt-6 text-right">P. Unit.</th>
                        <th class="pb-4 pr-8 pt-6 text-right">Subtotal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 border-t border-gray-50">
                    @foreach($group->movements as $movement)
                        <tr class="group transition-colors hover:bg-gray-50/50">
                            <td class="py-5 pl-8 text-sm font-bold text-[#1e293b]">
                                {{ $movement->product->name }}
                            </td>
                            <td class="py-5 font-mono text-xs font-bold text-gray-400">
                                {{ $movement->batch?->batch_code ?? '—' }}
                            </td>
                            <td class="py-5 text-right font-black text-[#1e293b]">
                                {{ number_format($movement->quantity, 2) }}
                            </td>
                            <td class="py-5 text-right font-bold text-[#e11d48]">
                                Bs. {{ number_format($movement->unit_cost ?? 0, 2) }}
                            </td>
                            <td class="py-5 pr-8 text-right font-black text-[#1e293b]">
                                Bs. {{ number_format(($movement->quantity ?? 0) * ($movement->unit_cost ?? 0), 2) }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Total footer --}}
        <div class="flex items-center justify-end gap-4 rounded-b-[2.5rem] border-t border-gray-50 bg-gray-50/30 px-8 py-5">
            <span class="text-[10px] font-bold uppercase tracking-widest text-gray-400">Total de la venta:</span>
            <span class="font-mono text-2xl font-black text-[#1e293b]">
                Bs. {{ number_format($doc?->total_amount ?? 0, 2) }}
            </span>
        </div>
    </div>

    {{-- Print footer --}}
    <div class="hidden print:block print:mt-8 print:border-t print:pt-4 print:text-center print:text-xs print:text-gray-500">
        <p>Gracias por su compra — {{ config('app.name') }}</p>
        <p class="mt-1">Total: <strong>Bs {{ number_format($doc?->total_amount ?? 0, 2) }}</strong></p>
    </div>

    {{-- Acciones --}}
    <div class="mt-8 flex flex-wrap items-center gap-3 print:hidden">
        <a href="{{ route('sales.index') }}"
           class="inline-flex items-center gap-2 rounded-2xl bg-gray-100 px-8 py-3 text-sm font-bold text-gray-500 transition-all hover:bg-gray-200 hover:text-[#1e293b]">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
            Historial
        </a>

        <a href="{{ route('sales.create') }}"
           class="flex h-11 items-center gap-2 rounded-xl bg-[#e11d48] px-8 text-sm font-bold text-white shadow-md transition-all hover:bg-[#be123c] active:scale-95">
            <span class="text-lg">+</span> Nueva Venta
        </a>

        @if(auth()->user()->isAdmin() && $isOpen)
            <form method="POST" action="{{ route('sales.cancel', $group->id) }}"
                onsubmit="return confirm('¿Cancelar venta {{ $doc?->doc_number }}? El stock se revertirá automáticamente.')"
                class="ml-auto">
                @csrf
                <button type="submit"
                    class="flex h-11 items-center rounded-2xl border border-red-200 bg-red-50 px-8 text-sm font-bold text-[#e11d48] transition-all hover:bg-red-100">
                    Cancelar Venta
                </button>
            </form>
        @endif
    </div>
@endsection