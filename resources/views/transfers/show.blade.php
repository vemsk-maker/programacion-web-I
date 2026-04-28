@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Detalle del Traslado" />

    {{-- Flash --}}
    @if(session('success'))
        <div class="mb-4 rounded-lg bg-success-50 border border-success-200 px-4 py-3 text-sm text-success-700 dark:bg-success-500/10 dark:border-success-500/20 dark:text-success-400">
            {{ session('success') }}
        </div>
    @endif

    @php
        // In a transfer, out-movements share from_location; in-movements share to_location.
        $outMove = $group->movements->first(fn ($m) => $m->from_location_id !== null);
        $inMove  = $group->movements->first(fn ($m) => $m->to_location_id !== null);
        // Unique products: deduplicate pairs (each product has one out + one in movement)
        $uniqueMovements = $group->movements
            ->where('from_location_id', '!=', null)
            ->values();
    @endphp

    {{-- ── Header cards ── --}}
    <div class="mb-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        {{-- Date --}}
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <p class="text-xs font-medium uppercase tracking-wide text-gray-400 dark:text-gray-500">Fecha</p>
            <p class="mt-1 text-base font-semibold text-gray-800 dark:text-white/90">
                {{ $group->created_at->format('d/m/Y') }}
            </p>
            <p class="text-xs text-gray-400 dark:text-gray-500">{{ $group->created_at->format('H:i') }}</p>
        </div>

        {{-- Origin --}}
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <p class="text-xs font-medium uppercase tracking-wide text-gray-400 dark:text-gray-500">Origen</p>
            <p class="mt-1 text-base font-semibold text-gray-800 dark:text-white/90">
                {{ $outMove?->fromLocation?->name ?? '—' }}
            </p>
        </div>

        {{-- Destination --}}
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <p class="text-xs font-medium uppercase tracking-wide text-gray-400 dark:text-gray-500">Destino</p>
            <p class="mt-1 text-base font-semibold text-gray-800 dark:text-white/90">
                {{ $inMove?->toLocation?->name ?? '—' }}
            </p>
        </div>

        {{-- User --}}
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <p class="text-xs font-medium uppercase tracking-wide text-gray-400 dark:text-gray-500">Registrado por</p>
            <p class="mt-1 text-base font-semibold text-gray-800 dark:text-white/90">
                {{ $group->user?->name ?? '—' }}
            </p>
        </div>
    </div>

    {{-- ── Lines table ── --}}
    <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4 dark:border-gray-800">
            <h3 class="text-base font-medium text-gray-800 dark:text-white/90">Productos trasladados</h3>
            <button type="button" onclick="window.print()"
                class="rounded-lg bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200 dark:bg-white/10 dark:text-white/80 dark:hover:bg-white/20 print:hidden">
                🖨 Imprimir nota
            </button>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 dark:border-gray-800">
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Producto</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Lote</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Vencimiento</th>
                        <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Cantidad</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @foreach($uniqueMovements as $movement)
                        <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.02]">
                            <td class="px-6 py-3 font-medium text-gray-800 dark:text-white/90">
                                {{ $movement->product->name }}
                            </td>
                            <td class="px-6 py-3 font-mono text-xs text-gray-500 dark:text-gray-400">
                                {{ $movement->batch?->batch_code ?? '—' }}
                            </td>
                            <td class="px-6 py-3 text-gray-500 dark:text-gray-400">
                                @if($movement->batch?->expiration_date)
                                    @php $exp = $movement->batch->expiration_date; @endphp
                                    <span class="{{ $exp->isPast() ? 'text-error-600 dark:text-error-400 font-medium' : ($exp->diffInDays() < 30 ? 'text-warning-600 dark:text-warning-400' : '') }}">
                                        {{ $exp->format('d/m/Y') }}
                                    </span>
                                @else
                                    —
                                @endif
                            </td>
                            <td class="px-6 py-3 text-right font-medium text-gray-800 dark:text-white/90">
                                {{ number_format($movement->quantity, 2) }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Notes --}}
    @if($group->notes)
        <div class="mt-4 rounded-xl border border-gray-200 bg-white px-6 py-4 dark:border-gray-800 dark:bg-white/[0.03]">
            <p class="text-xs font-medium uppercase tracking-wide text-gray-400 dark:text-gray-500">Notas</p>
            <p class="mt-1 text-sm text-gray-700 dark:text-gray-300">{{ $group->notes }}</p>
        </div>
    @endif

    {{-- Print-only header (hidden on screen) --}}
    <div class="hidden print:block print:mb-8">
        <h1 class="text-xl font-bold">Nota de Traslado</h1>
        <p>Origen: {{ $outMove?->fromLocation?->name }} → Destino: {{ $inMove?->toLocation?->name }}</p>
        <p>Fecha: {{ $group->created_at->format('d/m/Y H:i') }} | Responsable: {{ $group->user?->name }}</p>
    </div>

    <div class="mt-5 print:hidden">
        <a href="{{ route('inventory.transfers.index') }}"
           class="rounded-lg bg-gray-100 px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-200 dark:bg-white/10 dark:text-white/80 dark:hover:bg-white/20">
            ← Volver a Traslados
        </a>
    </div>
@endsection
