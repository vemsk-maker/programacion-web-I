@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Detalle de Compra" />

    {{-- Flash --}}
    @if(session('success'))
        <div class="mb-4 rounded-lg bg-success-50 border border-success-200 px-4 py-3 text-sm text-success-700 dark:bg-success-500/10 dark:border-success-500/20 dark:text-success-400">
            {{ session('success') }}
        </div>
    @endif

    @php
        $document       = $group->document;
        $firstMovement  = $group->movements->first();
        $location       = $firstMovement?->toLocation;
        $supplier       = $firstMovement?->batch?->supplier;
    @endphp

    {{-- ── Header card ── --}}
    <div class="mb-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        {{-- Doc number / status --}}
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <p class="text-xs font-medium uppercase tracking-wide text-gray-400 dark:text-gray-500">Documento</p>
            <p class="mt-1 font-mono text-lg font-semibold text-gray-800 dark:text-white/90">
                {{ $document?->doc_number ?? '—' }}
            </p>
            @if($document)
                @if($document->status->value === 'closed')
                    <span class="mt-1 inline-flex items-center rounded-full bg-success-50 px-2.5 py-0.5 text-xs font-medium text-success-700 dark:bg-success-500/10 dark:text-success-400">
                        Cerrado
                    </span>
                @elseif($document->status->value === 'cancelled')
                    <span class="mt-1 inline-flex items-center rounded-full bg-error-50 px-2.5 py-0.5 text-xs font-medium text-error-600 dark:bg-error-500/10 dark:text-error-400">
                        Anulado
                    </span>
                @else
                    <span class="mt-1 inline-flex items-center rounded-full bg-warning-50 px-2.5 py-0.5 text-xs font-medium text-warning-600 dark:bg-warning-500/10 dark:text-warning-400">
                        Abierto
                    </span>
                @endif
            @endif
        </div>

        {{-- Fecha --}}
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <p class="text-xs font-medium uppercase tracking-wide text-gray-400 dark:text-gray-500">Fecha</p>
            <p class="mt-1 text-base font-semibold text-gray-800 dark:text-white/90">
                {{ $group->created_at->format('d/m/Y') }}
            </p>
            <p class="text-xs text-gray-400 dark:text-gray-500">{{ $group->created_at->format('H:i') }} — {{ $group->user->name }}</p>
        </div>

        {{-- Proveedor --}}
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <p class="text-xs font-medium uppercase tracking-wide text-gray-400 dark:text-gray-500">Proveedor</p>
            <p class="mt-1 text-base font-semibold text-gray-800 dark:text-white/90">
                {{ $supplier?->name ?? '—' }}
            </p>
            @if($supplier?->nit)
                <p class="text-xs text-gray-400 dark:text-gray-500">NIT: {{ $supplier->nit }}</p>
            @endif
        </div>

        {{-- Ubicación destino --}}
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <p class="text-xs font-medium uppercase tracking-wide text-gray-400 dark:text-gray-500">Destino</p>
            <p class="mt-1 text-base font-semibold text-gray-800 dark:text-white/90">
                {{ $location?->name ?? '—' }}
            </p>
            @if($group->reference_doc)
                <p class="text-xs text-gray-400 dark:text-gray-500">Ref: {{ $group->reference_doc }}</p>
            @endif
        </div>
    </div>

    {{-- ── Lines table ── --}}
    <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="border-b border-gray-100 px-6 py-4 dark:border-gray-800">
            <h3 class="text-base font-medium text-gray-800 dark:text-white/90">Productos ingresados</h3>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 dark:border-gray-800">
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Producto</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Lote</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Vencimiento</th>
                        <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Cantidad</th>
                        <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Costo Unit.</th>
                        <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Subtotal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @foreach($group->movements as $movement)
                        @php $subtotal = (float)$movement->quantity * (float)$movement->unit_cost; @endphp
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
                            <td class="px-6 py-3 text-right text-gray-600 dark:text-gray-300">
                                Bs. {{ number_format($movement->unit_cost, 2) }}
                            </td>
                            <td class="px-6 py-3 text-right font-medium text-gray-800 dark:text-white/90">
                                Bs. {{ number_format($subtotal, 2) }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="border-t border-gray-200 dark:border-gray-700">
                        <td colspan="5" class="px-6 py-4 text-right text-sm font-semibold text-gray-700 dark:text-gray-300">
                            Total de la compra:
                        </td>
                        <td class="px-6 py-4 text-right text-base font-bold text-gray-900 dark:text-white">
                            Bs. {{ number_format($document?->total_amount ?? $group->movements->sum(fn($m) => $m->quantity * $m->unit_cost), 2) }}
                        </td>
                    </tr>
                </tfoot>
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

    {{-- Back action --}}
    <div class="mt-5">
        <a href="{{ route('inventory.purchases.index') }}"
           class="rounded-lg bg-gray-100 px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-200 dark:bg-white/10 dark:text-white/80 dark:hover:bg-white/20">
            ← Volver a Compras
        </a>
    </div>
@endsection
