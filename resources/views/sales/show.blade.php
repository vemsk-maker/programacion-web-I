@extends('layouts.app')

@section('content')
    @php
        $doc      = $group->document;
        $location = $group->movements->first()?->fromLocation;
        $isOpen   = $doc?->status?->value === 'open';
    @endphp

    {{-- ── Alerts ── --}}
    @if(session('success'))
        <div class="mb-4 rounded-lg bg-success-50 border border-success-200 px-4 py-3 text-sm text-success-700 dark:bg-success-500/10 dark:border-success-500/20 dark:text-success-400 print:hidden">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="mb-4 rounded-lg bg-error-50 border border-error-200 px-4 py-3 text-sm text-error-700 dark:bg-error-500/10 dark:border-error-500/20 dark:text-error-400 print:hidden">
            {{ session('error') }}
        </div>
    @endif

    {{-- ── Print header (hidden on screen) ── --}}
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

    <x-common.page-breadcrumb pageTitle="Recibo de Venta" />

    {{-- ── Header cards (screen only) ── --}}
    <div class="mb-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4 print:hidden">
        {{-- Doc number --}}
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <p class="text-xs font-medium uppercase tracking-wide text-gray-400 dark:text-gray-500">N° Recibo</p>
            <p class="mt-1 font-mono text-base font-bold text-gray-800 dark:text-white/90">
                {{ $doc?->doc_number ?? '—' }}
            </p>
            @if($doc?->status?->value === 'open')
                <span class="mt-1 inline-flex items-center rounded-full bg-success-50 px-2 py-0.5 text-xs font-medium text-success-700 dark:bg-success-500/10 dark:text-success-400">Abierto</span>
            @elseif($doc?->status?->value === 'cancelled')
                <span class="mt-1 inline-flex items-center rounded-full bg-error-50 px-2 py-0.5 text-xs font-medium text-error-600 dark:bg-error-500/10 dark:text-error-400">Cancelado</span>
            @endif
        </div>

        {{-- Date / time --}}
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <p class="text-xs font-medium uppercase tracking-wide text-gray-400 dark:text-gray-500">Fecha y Hora</p>
            <p class="mt-1 text-base font-semibold text-gray-800 dark:text-white/90">
                {{ $group->created_at->format('d/m/Y') }}
            </p>
            <p class="text-xs text-gray-400 dark:text-gray-500">{{ $group->created_at->format('H:i') }}</p>
        </div>

        {{-- Location --}}
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <p class="text-xs font-medium uppercase tracking-wide text-gray-400 dark:text-gray-500">Sucursal</p>
            <p class="mt-1 text-base font-semibold text-gray-800 dark:text-white/90">
                {{ $location?->name ?? '—' }}
            </p>
        </div>

        {{-- Cashier --}}
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <p class="text-xs font-medium uppercase tracking-wide text-gray-400 dark:text-gray-500">Cajero</p>
            <p class="mt-1 text-base font-semibold text-gray-800 dark:text-white/90">
                {{ $group->user?->name ?? '—' }}
            </p>
        </div>
    </div>

    {{-- ── Client info (screen card, only if present) ── --}}
    @if($doc?->client_name || $doc?->client_nit)
        <div class="mb-5 rounded-2xl border border-gray-200 bg-white px-5 py-4 dark:border-gray-800 dark:bg-white/[0.03] print:hidden">
            <p class="mb-2 text-xs font-medium uppercase tracking-wide text-gray-400 dark:text-gray-500">Datos del Cliente</p>
            <div class="flex flex-wrap gap-6 text-sm">
                @if($doc->client_name)
                    <div>
                        <span class="text-gray-400 dark:text-gray-500">Nombre: </span>
                        <span class="font-medium text-gray-800 dark:text-white/90">{{ $doc->client_name }}</span>
                    </div>
                @endif
                @if($doc->client_nit)
                    <div>
                        <span class="text-gray-400 dark:text-gray-500">NIT/CI: </span>
                        <span class="font-medium text-gray-800 dark:text-white/90">{{ $doc->client_nit }}</span>
                    </div>
                @endif
            </div>
        </div>
    @endif

    {{-- ── Products table ── --}}
    <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] print:border-none print:shadow-none">
        <div class="flex items-center justify-between border-b border-gray-100 px-5 py-4 dark:border-gray-800 print:hidden">
            <h3 class="text-base font-medium text-gray-800 dark:text-white/90">Productos vendidos</h3>
            <button type="button" onclick="window.print()"
                class="rounded-lg bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200 dark:bg-white/10 dark:text-white/80 dark:hover:bg-white/20">
                🖨 Imprimir recibo
            </button>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 dark:border-gray-800">
                        <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Producto</th>
                        <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Lote</th>
                        <th class="px-5 py-3 text-right text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Cant.</th>
                        <th class="px-5 py-3 text-right text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">P. Unit.</th>
                        <th class="px-5 py-3 text-right text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Subtotal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @foreach($group->movements as $movement)
                        <tr>
                            <td class="px-5 py-3 font-medium text-gray-800 dark:text-white/90">
                                {{ $movement->product->name }}
                            </td>
                            <td class="px-5 py-3 font-mono text-xs text-gray-500 dark:text-gray-400">
                                {{ $movement->batch?->batch_code ?? '—' }}
                            </td>
                            <td class="px-5 py-3 text-right text-gray-700 dark:text-gray-300">
                                {{ number_format($movement->quantity, 2) }}
                            </td>
                            <td class="px-5 py-3 text-right text-gray-700 dark:text-gray-300">
                                Bs {{ number_format($movement->unit_cost ?? 0, 2) }}
                            </td>
                            <td class="px-5 py-3 text-right font-medium text-gray-800 dark:text-white/90">
                                Bs {{ number_format(($movement->quantity ?? 0) * ($movement->unit_cost ?? 0), 2) }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="border-t-2 border-gray-200 dark:border-gray-700">
                        <td colspan="4" class="px-5 py-3 text-right text-sm font-semibold text-gray-800 dark:text-white/90">TOTAL</td>
                        <td class="px-5 py-3 text-right text-base font-bold text-gray-900 dark:text-white">
                            Bs {{ number_format($doc?->total_amount ?? 0, 2) }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    {{-- ── Print-only footer ── --}}
    <div class="hidden print:block print:mt-8 print:border-t print:pt-4 print:text-center print:text-xs print:text-gray-500">
        <p>Gracias por su compra — {{ config('app.name') }}</p>
        <p class="mt-1">Total: <strong>Bs {{ number_format($doc?->total_amount ?? 0, 2) }}</strong></p>
    </div>

    {{-- ── Screen actions ── --}}
    <div class="mt-5 flex flex-wrap items-center gap-3 print:hidden">
        <a href="{{ route('sales.index') }}"
            class="rounded-lg bg-gray-100 px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-200 dark:bg-white/10 dark:text-white/80 dark:hover:bg-white/20">
            ← Historial
        </a>
        <a href="{{ route('sales.create') }}"
            class="rounded-lg bg-brand-500 px-5 py-2.5 text-sm font-medium text-white hover:bg-brand-600">
            + Nueva Venta
        </a>

        @if(auth()->user()->isAdmin() && $isOpen)
            <form method="POST" action="{{ route('sales.cancel', $group->id) }}"
                onsubmit="return confirm('¿Cancelar venta {{ $doc?->doc_number }}? El stock se revertirá automáticamente.')"
                class="ml-auto">
                @csrf
                <button type="submit"
                    class="rounded-lg border border-error-300 bg-error-50 px-5 py-2.5 text-sm font-medium text-error-600 hover:bg-error-100 dark:border-error-500/20 dark:bg-error-500/10 dark:text-error-400 dark:hover:bg-error-500/20">
                    Cancelar Venta
                </button>
            </form>
        @endif
    </div>
@endsection
