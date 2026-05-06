@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Detalle del Producto" />

    {{-- Flash - Estilo Ayma --}}
    @if(session('success'))
        <div class="mb-6 rounded-2xl bg-emerald-50 border border-emerald-100 px-5 py-4 text-sm font-bold text-emerald-600 shadow-sm">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid gap-6 lg:grid-cols-3">
        {{-- Main info --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Forzamos el contenedor blanco y limpio --}}
            <div class="rounded-3xl bg-white p-8 border border-gray-100 shadow-sm">
                <h2 class="text-2xl font-black text-[#1e293b] mb-4">{{ $product->name }}</h2>
                
                <div class="space-y-4 text-sm">
                    @if($product->description)
                        <p class="text-gray-500 font-medium leading-relaxed">{{ $product->description }}</p>
                    @endif
                    
                    <div class="grid grid-cols-2 gap-6 pt-4 border-t border-gray-50">
                        <div>
                            <span class="text-[10px] font-black uppercase tracking-widest text-[#1e293b]/50">Categoría</span>
                            <p class="mt-1 text-sm font-bold text-[#1e293b]">{{ $product->category->name }}</p>
                        </div>
                        <div>
                            <span class="text-[10px] font-black uppercase tracking-widest text-[#1e293b]/50">Unidad de Medida</span>
                            <p class="mt-1 text-sm font-bold text-[#1e293b]">{{ $product->unit_of_measure }}</p>
                        </div>
                        <div>
                            <span class="text-[10px] font-black uppercase tracking-widest text-[#1e293b]/50">Precio de Venta</span>
                            <p class="mt-1 text-sm font-bold text-[#1e293b]">
                                @if($product->sale_price !== null)
                                    Bs {{ number_format($product->sale_price, 2) }}
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </p>
                        </div>
                        <div>
                            <span class="text-[10px] font-black uppercase tracking-widest text-[#1e293b]/50">Control por Lotes</span>
                            <p class="mt-1 text-sm font-bold">
                                @if($product->use_batches)
                                    <span class="inline-flex items-center rounded-lg bg-[#e11d48]/10 px-2.5 py-1 text-[10px] font-black uppercase text-[#e11d48]">PEPS activo</span>
                                @else
                                    <span class="text-gray-400">Sin control</span>
                                @endif
                            </p>
                        </div>
                        <div>
                            <span class="text-[10px] font-black uppercase tracking-widest text-[#1e293b]/50">Estado</span>
                            <p class="mt-1 text-sm font-bold">
                                @if($product->active)
                                    <span class="inline-flex items-center rounded-lg bg-emerald-50 px-2.5 py-1 text-[10px] font-black uppercase text-emerald-600">Activo</span>
                                @else
                                    <span class="inline-flex items-center rounded-lg bg-gray-100 px-2.5 py-1 text-[10px] font-black uppercase text-gray-500">Inactivo</span>
                                @endif
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Botones de Acción --}}
                <div class="mt-8 flex items-center gap-3">
                    <a href="{{ route('products.edit', $product) }}"
                       class="rounded-2xl bg-[#e11d48] px-6 py-3 text-[11px] font-black uppercase tracking-widest text-white shadow-lg shadow-red-500/20 hover:bg-[#be123c] transition-all active:scale-95">
                        Editar Producto
                    </a>
                    <a href="{{ route('products.index') }}"
                       class="rounded-2xl bg-gray-100 px-6 py-3 text-[11px] font-black uppercase tracking-widest text-gray-500 hover:bg-gray-200 transition-all">
                        Volver
                    </a>
                </div>
            </div>

            {{-- Stock by location --}}
            <div class="rounded-3xl border border-gray-100 bg-white shadow-sm overflow-hidden">
                <div class="border-b border-gray-50 px-8 py-5">
                    <h3 class="text-xs font-black uppercase tracking-[2px] text-[#1e293b]">Stock por Ubicación</h3>
                </div>
                <div class="overflow-x-auto px-4 pb-4">
                    <table class="w-full">
                        <thead>
                            <tr class="text-[10px] font-black uppercase tracking-widest text-gray-400">
                                <th class="px-4 py-4 text-left">Ubicación</th>
                                @if($product->use_batches)
                                    <th class="px-4 py-4 text-left">Lote</th>
                                    <th class="px-4 py-4 text-left">Vencimiento</th>
                                @endif
                                <th class="px-4 py-4 text-right">Cantidad</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @forelse($stock as $item)
                                <tr class="hover:bg-gray-50/50 transition-colors">
                                    <td class="px-4 py-4 text-sm font-bold text-[#1e293b]">{{ $item->location->name }}</td>
                                    @if($product->use_batches)
                                        <td class="px-4 py-4 text-sm font-medium text-gray-500">{{ $item->batch?->batch_code ?? '—' }}</td>
                                        <td class="px-4 py-4 text-sm font-bold">
                                            @if($item->batch?->expiration_date)
                                                <span class="{{ $item->batch->expiration_date->isPast() ? 'text-[#e11d48]' : ($item->batch->expiration_date->diffInDays() < 30 ? 'text-amber-500' : 'text-[#1e293b]') }}">
                                                    {{ $item->batch->expiration_date->format('d/m/Y') }}
                                                </span>
                                            @else
                                                <span class="text-gray-300">—</span>
                                            @endif
                                        </td>
                                    @endif
                                    <td class="px-4 py-4 text-right text-sm font-black text-[#e11d48]">
                                        {{ number_format($item->quantity, 2) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ $product->use_batches ? 4 : 2 }}" class="px-6 py-10 text-center text-sm font-medium text-gray-400 italic">
                                        Sin stock registrado en el sistema.
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
            <div class="rounded-3xl border border-gray-100 bg-white shadow-sm overflow-hidden">
                <div class="border-b border-gray-50 px-8 py-5">
                    <h3 class="text-xs font-black uppercase tracking-[2px] text-[#1e293b]">Códigos de Barras</h3>
                </div>
                {{-- Fondo gris ultra claro para la lista interna como en los forms anteriores --}}
                <div class="bg-gray-50/50 divide-y divide-white">
                    @forelse($product->barcodes as $barcode)
                        <div class="flex items-center justify-between px-8 py-4">
                            <span class="font-mono text-sm font-bold text-[#1e293b]">{{ $barcode->barcode }}</span>
                            <span class="rounded-lg bg-white border border-gray-100 px-2 py-1 text-[10px] font-black text-[#e11d48]">× {{ $barcode->units_per_scan }}</span>
                        </div>
                    @empty
                        <p class="px-8 py-8 text-center text-xs font-medium text-gray-400">Sin códigos registrados.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection