@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Detalle del Producto" />

    {{-- Flash --}}
    @if(session('success'))
        <div class="mb-4 rounded-lg bg-success-50 border border-success-200 px-4 py-3 text-sm text-success-700 dark:bg-success-500/10 dark:border-success-500/20 dark:text-success-400">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid gap-6 lg:grid-cols-3">
        {{-- Main info --}}
        <div class="lg:col-span-2 space-y-6">
            <x-common.component-card :title="$product->name">
                <div class="space-y-3 text-sm">
                    @if($product->description)
                        <p class="text-gray-600 dark:text-gray-400">{{ $product->description }}</p>
                    @endif
                    <div class="grid grid-cols-2 gap-4 pt-2">
                        <div>
                            <span class="text-xs font-medium uppercase tracking-wide text-gray-400 dark:text-gray-500">Categoría</span>
                            <p class="mt-0.5 text-gray-700 dark:text-gray-300">{{ $product->category->name }}</p>
                        </div>
                        <div>
                            <span class="text-xs font-medium uppercase tracking-wide text-gray-400 dark:text-gray-500">Unidad de Medida</span>
                            <p class="mt-0.5 text-gray-700 dark:text-gray-300">{{ $product->unit_of_measure }}</p>
                        </div>
                        <div>
                            <span class="text-xs font-medium uppercase tracking-wide text-gray-400 dark:text-gray-500">Control por Lotes</span>
                            <p class="mt-0.5">
                                @if($product->use_batches)
                                    <span class="inline-flex items-center rounded-full bg-brand-50 px-2.5 py-0.5 text-xs font-medium text-brand-600 dark:bg-brand-500/10 dark:text-brand-400">PEPS activo</span>
                                @else
                                    <span class="text-gray-500 dark:text-gray-400">Sin control de lotes</span>
                                @endif
                            </p>
                        </div>
                        <div>
                            <span class="text-xs font-medium uppercase tracking-wide text-gray-400 dark:text-gray-500">Estado</span>
                            <p class="mt-0.5">
                                @if($product->active)
                                    <span class="inline-flex items-center rounded-full bg-success-50 px-2.5 py-0.5 text-xs font-medium text-success-700 dark:bg-success-500/10 dark:text-success-400">Activo</span>
                                @else
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-500 dark:bg-white/10 dark:text-gray-400">Inactivo</span>
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
                <div class="mt-5 flex items-center gap-3">
                    <a href="{{ route('products.edit', $product) }}"
                       class="rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600">
                        Editar
                    </a>
                    <a href="{{ route('products.index') }}"
                       class="rounded-lg bg-gray-100 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-200 dark:bg-white/10 dark:text-white/80 dark:hover:bg-white/20">
                        Volver
                    </a>
                </div>
            </x-common.component-card>

            {{-- Stock by location --}}
            <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="border-b border-gray-100 px-6 py-4 dark:border-gray-800">
                    <h3 class="text-base font-medium text-gray-800 dark:text-white/90">Stock por Ubicación</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-100 dark:border-gray-800">
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Ubicación</th>
                                @if($product->use_batches)
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Lote</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Vencimiento</th>
                                @endif
                                <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Cantidad</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @forelse($stock as $item)
                                <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.02]">
                                    <td class="px-6 py-3 text-gray-700 dark:text-gray-300">{{ $item->location->name }}</td>
                                    @if($product->use_batches)
                                        <td class="px-6 py-3 text-gray-500 dark:text-gray-400">
                                            {{ $item->batch?->batch_code ?? '—' }}
                                        </td>
                                        <td class="px-6 py-3 text-gray-500 dark:text-gray-400">
                                            @if($item->batch?->expiration_date)
                                                <span class="{{ $item->batch->expiration_date->isPast() ? 'text-error-600 dark:text-error-400' : ($item->batch->expiration_date->diffInDays() < 30 ? 'text-warning-600 dark:text-warning-400' : '') }}">
                                                    {{ $item->batch->expiration_date->format('d/m/Y') }}
                                                </span>
                                            @else
                                                —
                                            @endif
                                        </td>
                                    @endif
                                    <td class="px-6 py-3 text-right font-medium text-gray-800 dark:text-white/90">
                                        {{ number_format($item->quantity, 2) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ $product->use_batches ? 4 : 2 }}" class="px-6 py-8 text-center text-sm text-gray-400 dark:text-gray-500">
                                        Sin stock registrado.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Sidebar: barcodes --}}
        <div>
            <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="border-b border-gray-100 px-6 py-4 dark:border-gray-800">
                    <h3 class="text-base font-medium text-gray-800 dark:text-white/90">Códigos de Barras</h3>
                </div>
                <div class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($product->barcodes as $barcode)
                        <div class="flex items-center justify-between px-6 py-3">
                            <span class="font-mono text-sm text-gray-800 dark:text-white/90">{{ $barcode->barcode }}</span>
                            <span class="text-xs text-gray-400 dark:text-gray-500">× {{ $barcode->units_per_scan }}</span>
                        </div>
                    @empty
                        <p class="px-6 py-6 text-center text-sm text-gray-400 dark:text-gray-500">Sin códigos registrados.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection
