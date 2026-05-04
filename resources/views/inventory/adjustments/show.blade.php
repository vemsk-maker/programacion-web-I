@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Detalle Ajuste #{{ $adjustment->id }}" />

    <div class="max-w-3xl space-y-6">

        {{-- Encabezado --}}
        <div class="rounded-3xl border border-gray-100 bg-white shadow-sm p-6 space-y-4">
            <div class="flex items-start justify-between">
                <div>
                    <h3 class="text-lg font-black text-[#1e293b] uppercase tracking-tight">
                        Ajuste de Inventario #{{ $adjustment->id }}
                    </h3>
                    <p class="text-sm text-gray-400 mt-0.5">{{ $adjustment->created_at->format('d/m/Y H:i:s') }}</p>
                </div>
                <a href="{{ route('inventory.adjustments.index') }}"
                   class="rounded-xl bg-gray-100 px-4 py-2 text-sm font-black text-gray-600 hover:bg-gray-200 transition-all">
                    ← Volver
                </a>
            </div>

            <div class="grid grid-cols-2 gap-4 text-sm">
                <div class="rounded-xl bg-gray-50 p-3">
                    <p class="text-[11px] font-black uppercase text-gray-400 mb-1">Ubicación</p>
                    <p class="font-bold text-[#1e293b]">{{ $adjustment->originLocation?->name ?? '—' }}</p>
                </div>
                <div class="rounded-xl bg-gray-50 p-3">
                    <p class="text-[11px] font-black uppercase text-gray-400 mb-1">Registrado por</p>
                    <p class="font-bold text-[#1e293b]">{{ $adjustment->user?->name ?? '—' }}</p>
                </div>
                @if ($adjustment->notes)
                    <div class="col-span-2 rounded-xl bg-yellow-50 border border-yellow-100 p-3">
                        <p class="text-[11px] font-black uppercase text-yellow-600 mb-1">Notas</p>
                        <p class="font-semibold text-gray-700">{{ $adjustment->notes }}</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- Líneas --}}
        <div class="rounded-3xl border border-gray-100 bg-white shadow-sm overflow-hidden">
            <div class="border-b border-gray-50 px-6 py-4">
                <h3 class="text-base font-black text-[#1e293b] uppercase tracking-tight">Líneas de Movimiento</h3>
            </div>
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-gray-50">
                        <th class="px-6 py-3 text-[11px] font-black uppercase tracking-wider text-gray-400 bg-gray-50/50">Producto</th>
                        <th class="px-6 py-3 text-[11px] font-black uppercase tracking-wider text-gray-400 bg-gray-50/50 text-center">Tipo</th>
                        <th class="px-6 py-3 text-[11px] font-black uppercase tracking-wider text-gray-400 bg-gray-50/50 text-right">Cantidad</th>
                        <th class="px-6 py-3 text-[11px] font-black uppercase tracking-wider text-gray-400 bg-gray-50/50 text-right">Costo unit.</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach ($adjustment->movements as $mov)
                        @php
                            $isPositive = ! is_null($mov->to_location_id);
                        @endphp
                        <tr class="hover:bg-gray-50/30">
                            <td class="px-6 py-3 text-sm font-bold text-[#1e293b]">{{ $mov->product?->name ?? '—' }}</td>
                            <td class="px-6 py-3 text-center">
                                <span class="inline-block rounded-full px-3 py-0.5 text-xs font-black
                                    {{ $isPositive ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                    {{ $isPositive ? 'Ingreso' : 'Retiro' }}
                                </span>
                            </td>
                            <td class="px-6 py-3 text-right text-sm font-black
                                {{ $isPositive ? 'text-green-600' : 'text-red-600' }}">
                                {{ $isPositive ? '+' : '-' }}{{ $mov->quantity }}
                            </td>
                            <td class="px-6 py-3 text-right text-sm text-gray-500">
                                {{ $mov->unit_cost ? 'Bs. ' . number_format($mov->unit_cost, 2) : '—' }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
