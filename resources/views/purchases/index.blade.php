@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Compras" />

    {{-- Notificaciones Flash --}}
    @if(session('success'))
        <div class="mb-6 flex items-center gap-3 rounded-2xl bg-emerald-50 border border-emerald-100 px-5 py-3 text-sm font-bold text-emerald-700">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
            {{ session('success') }}
        </div>
    @endif

    <div class="rounded-[2.5rem] border border-gray-100 bg-white p-8 shadow-sm">
        {{-- Header + filtros --}}
        <div class="mb-8 flex flex-wrap items-center justify-between gap-6 border-b border-gray-50 pb-6">
            <div>
                <h3 class="text-2xl font-bold text-[#1e293b]">Registro de Compras</h3>
                <p class="text-sm text-gray-500">Gestión de abastecimiento e inventario</p>
            </div>
            
            <div class="flex flex-wrap items-center gap-3">
                <form method="GET" action="{{ route('inventory.purchases.index') }}" class="flex flex-wrap items-center gap-3">
                    {{-- Rango de Fechas --}}
                    <div class="flex items-center gap-2 rounded-xl border border-gray-200 bg-gray-50/50 px-3">
                        <input type="date" name="date_from" value="{{ request('date_from') }}"
                            class="h-10 bg-transparent text-sm text-gray-700 focus:outline-none" />
                        <span class="text-gray-400">/</span>
                        <input type="date" name="date_to" value="{{ request('date_to') }}"
                            class="h-10 bg-transparent text-sm text-gray-700 focus:outline-none" />
                    </div>

                    {{-- Proveedor filter --}}
                    <div x-data="{ isOptionSelected: {{ request('supplier_id') ? 'true' : 'false' }} }" class="relative h-11 w-56">
                        <select name="supplier_id"
                            class="h-full w-full appearance-none rounded-xl border border-gray-200 bg-white px-4 pr-10 text-sm text-gray-700 focus:border-gray-400 focus:outline-none transition-all"
                            @change="isOptionSelected = true">
                            <option value="">Todos los proveedores</option>
                            @foreach($suppliers as $supplier)
                                <option value="{{ $supplier->id }}" {{ request('supplier_id') == $supplier->id ? 'selected' : '' }}>
                                    {{ $supplier->name }}
                                </option>
                            @endforeach
                        </select>
                        <span class="pointer-events-none absolute top-1/2 right-4 -translate-y-1/2 text-gray-400">
                            <svg width="16" height="16" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M4.79175 7.396L10.0001 12.6043L15.2084 7.396" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </span>
                    </div>

                    <button type="submit" class="h-11 rounded-xl bg-[#1e293b] px-6 text-sm font-bold text-white hover:bg-[#334155] transition-all">
                        Filtrar
                    </button>

                    @if(request()->hasAny(['date_from', 'date_to', 'supplier_id']))
                        <a href="{{ route('inventory.purchases.index') }}" class="text-sm font-bold text-gray-400 hover:text-[#e11d48] transition-colors">
                            Limpiar
                        </a>
                    @endif
                </form>

                <div class="h-8 w-px bg-gray-100 mx-2"></div>

                <a href="{{ route('inventory.purchases.create') }}"
                   class="flex h-11 items-center gap-2 rounded-xl bg-[#e11d48] px-6 text-sm font-bold text-white shadow-md transition-all hover:bg-[#be123c] active:scale-95">
                    <span class="text-lg">+</span> Nueva Compra
                </a>
            </div>
        </div>

        {{-- Table --}}
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="text-[10px] font-bold uppercase tracking-widest text-gray-400">
                        <th class="pb-4 pl-4">Doc.</th>
                        <th class="pb-4">Fecha</th>
                        <th class="pb-4">Proveedor</th>
                        <th class="pb-4">Ubicación</th>
                        <th class="pb-4">Referencia</th>
                        <th class="pb-4 text-right">Total</th>
                        <th class="pb-4 pr-4 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 border-t border-gray-50">
                    @forelse($purchases as $purchase)
                        @php
                            $firstMovement = $purchase->movements->first();
                            $location  = $firstMovement?->toLocation?->name ?? '—';
                            $supplier  = $firstMovement?->batch?->supplier?->name ?? '—';
                            $docNumber = $purchase->document?->doc_number ?? '—';
                            $total     = $purchase->document?->total_amount;
                        @endphp
                        <tr class="group transition-colors hover:bg-gray-50/50">
                            <td class="py-5 pl-4">
                                <span class="font-mono text-xs font-bold text-gray-400">#{{ $docNumber }}</span>
                            </td>
                            <td class="py-5">
                                <div class="flex flex-col">
                                    <span class="text-sm font-bold text-[#1e293b]">{{ $purchase->created_at->format('d/m/Y') }}</span>
                                    <span class="text-[10px] text-gray-400">{{ $purchase->created_at->format('H:i') }}</span>
                                </div>
                            </td>
                            <td class="py-5 text-sm font-semibold text-gray-700">{{ $supplier }}</td>
                            <td class="py-5">
                                <span class="inline-flex items-center rounded-lg bg-gray-100 px-3 py-1 text-[10px] font-bold text-gray-500 uppercase">
                                    {{ $location }}
                                </span>
                            </td>
                            <td class="py-5 text-sm text-gray-500">
                                {{ $purchase->reference_doc ?? '—' }}
                            </td>
                            <td class="py-5 text-right font-bold text-[#1e293b]">
                                @if($total !== null)
                                    Bs. {{ number_format($total, 2) }}
                                @else
                                    —
                                @endif
                            </td>
                            <td class="py-5 pr-4 text-right">
                                <a href="{{ route('inventory.purchases.show', $purchase->id) }}"
                                   class="text-[11px] font-bold text-blue-500 hover:text-blue-700 transition-colors uppercase tracking-tighter">
                                    Ver Detalle
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-20 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <p class="text-sm font-medium text-gray-400 italic">No hay compras registradas en este periodo.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($purchases->hasPages())
            <div class="mt-8 border-t border-gray-50 pt-6">
                {{ $purchases->links() }}
            </div>
        @endif
    </div>
@endsection