@extends('layouts.app')

@section('content')
@php
    $hour = now()->hour;
    $greeting = $hour < 12 ? 'Buenos días' : ($hour < 19 ? 'Buenas tardes' : 'Buenas noches');
    $fechaHoy = \Carbon\Carbon::now()->locale('es')->isoFormat('dddd, D [de] MMMM [de] YYYY');
@endphp

{{-- ── Encabezado de bienvenida ──────────────────────────────────────────────── --}}
<div class="mb-6 flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="text-2xl font-black text-[#1e293b] dark:text-white">
            {{ $greeting }}, {{ auth()->user()->name }}
        </h1>
        <p class="mt-0.5 text-sm font-medium capitalize text-gray-400">{{ $fechaHoy }}</p>
    </div>
    <div class="flex items-center gap-2">
        <a href="{{ route('inventory.purchases.create') }}"
            class="flex items-center gap-2 rounded-xl bg-[#e11d48] px-4 py-2.5 text-sm font-bold text-white shadow-sm transition-all hover:bg-[#be123c]">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
            </svg>
            Nueva compra
        </a>
        <a href="{{ route('sales.create') }}"
            class="flex items-center gap-2 rounded-xl border border-[#e11d48] px-4 py-2.5 text-sm font-bold text-[#e11d48] transition-all hover:bg-red-50 dark:hover:bg-red-950/30">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
            </svg>
            Nueva venta
        </a>
    </div>
</div>


{{-- ── Alerta crítica: lotes vencidos ──────────────────────────────────────────── --}}
@if($expiredCount > 0)
    <div class="mb-6 flex items-start gap-4 rounded-2xl border border-red-200 bg-red-50 px-6 py-5 dark:border-red-900/40 dark:bg-red-950/30">
        <svg class="mt-0.5 h-5 w-5 shrink-0 text-[#e11d48]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
        </svg>
        <div class="flex-1">
            <p class="font-bold text-[#e11d48]">
                Alerta crítica: {{ $expiredCount }} {{ Str::plural('lote', $expiredCount) }} vencido{{ $expiredCount !== 1 ? 's' : '' }} con stock disponible
            </p>
            <p class="mt-0.5 text-sm text-red-500">
                Estos productos no deben venderse. Revise el reporte de vencimientos inmediatamente.
            </p>
        </div>
        <a href="{{ route('reports.expirations', ['days' => 0]) }}"
            class="shrink-0 rounded-xl bg-[#e11d48] px-5 py-2 text-sm font-bold text-white transition-all hover:bg-[#be123c]">
            Ver vencidos
        </a>
    </div>
@endif

{{-- ── KPIs principales ─────────────────────────────────────────────────────────── --}}
<div class="mb-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">

    {{-- Ventas de hoy --}}
    <div class="relative overflow-hidden rounded-2xl bg-[#e11d48] p-6 text-white shadow-sm">
        <div class="flex items-start justify-between">
            <div>
                <p class="text-[10px] font-bold uppercase tracking-widest text-red-100">Ventas hoy</p>
                <p class="mt-2 font-mono text-4xl font-black">{{ $salesToday }}</p>
                <p class="mt-1 text-xs text-red-100">
                    Bs. {{ number_format($salesTodayAmount, 2) }} en total
                </p>
            </div>
            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-white/20">
                <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
            </div>
        </div>
        <p class="mt-3 text-[11px] text-red-100">{{ $salesWeek }} ventas esta semana</p>
    </div>

    {{-- Compras de hoy --}}
    <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
        <div class="flex items-start justify-between">
            <div>
                <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400">Compras hoy</p>
                <p class="mt-2 font-mono text-4xl font-black text-[#1e293b] dark:text-white">{{ $purchasesToday }}</p>
                <p class="mt-1 text-xs text-gray-400">ingresos de mercadería</p>
            </div>
            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-blue-100 dark:bg-blue-900/30">
                <svg class="h-6 w-6 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10"/>
                </svg>
            </div>
        </div>
        <a href="{{ route('inventory.purchases.index') }}" class="mt-3 block text-[11px] font-bold text-blue-500 hover:underline">
            Ver compras →
        </a>
    </div>

    {{-- Productos activos --}}
    <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
        <div class="flex items-start justify-between">
            <div>
                <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400">Productos activos</p>
                <p class="mt-2 font-mono text-4xl font-black text-[#1e293b] dark:text-white">{{ $activeProducts }}</p>
                <p class="mt-1 text-xs text-gray-400">en catálogo</p>
            </div>
            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-emerald-100 dark:bg-emerald-900/30">
                <svg class="h-6 w-6 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
            </div>
        </div>
        <a href="{{ route('products.index') }}" class="mt-3 block text-[11px] font-bold text-emerald-600 hover:underline">
            Ver productos →
        </a>
    </div>

    {{-- Movimientos de hoy --}}
    <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
        <div class="flex items-start justify-between">
            <div>
                <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400">Movimientos hoy</p>
                <p class="mt-2 font-mono text-4xl font-black text-[#1e293b] dark:text-white">{{ $todayMovements }}</p>
                <p class="mt-1 text-xs text-gray-400">registros de inventario</p>
            </div>
            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-purple-100 dark:bg-purple-900/30">
                <svg class="h-6 w-6 text-purple-600 dark:text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                </svg>
            </div>
        </div>
        <a href="{{ route('reports.movements', ['date_from' => today()->toDateString(), 'date_to' => today()->toDateString()]) }}"
            class="mt-3 block text-[11px] font-bold text-purple-600 hover:underline">
            Ver kardex →
        </a>
    </div>
</div>

{{-- ── Alertas de vencimiento ───────────────────────────────────────────────────── --}}
<div class="mb-6 grid gap-4 sm:grid-cols-2">

    {{-- Vencen en 30 días --}}
    <div class="flex items-center gap-5 rounded-2xl border px-6 py-5 shadow-sm
        {{ $expiringCount > 0 ? 'border-amber-100 bg-amber-50 dark:border-amber-900/40 dark:bg-amber-950/20' : 'border-gray-100 bg-white dark:border-gray-700 dark:bg-gray-800' }}">
        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl
            {{ $expiringCount > 0 ? 'bg-amber-100 dark:bg-amber-900/40' : 'bg-gray-100 dark:bg-gray-700' }}">
            <svg class="h-6 w-6 {{ $expiringCount > 0 ? 'text-amber-600' : 'text-gray-400' }}"
                fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <div class="flex-1">
            <p class="text-[10px] font-bold uppercase tracking-widest {{ $expiringCount > 0 ? 'text-amber-600' : 'text-gray-400' }}">
                Vencen en 30 días
            </p>
            <p class="mt-0.5 font-mono text-2xl font-black {{ $expiringCount > 0 ? 'text-amber-700' : 'text-gray-500 dark:text-gray-300' }}">
                {{ $expiringCount }} <span class="text-sm font-medium">lotes</span>
            </p>
        </div>
        <a href="{{ route('reports.expirations') }}"
            class="shrink-0 rounded-xl px-4 py-2 text-xs font-bold transition-all
            {{ $expiringCount > 0 ? 'bg-amber-500 text-white hover:bg-amber-600' : 'border border-gray-200 text-gray-500 hover:border-gray-300 dark:border-gray-600 dark:text-gray-400' }}">
            Ver reporte
        </a>
    </div>

    {{-- Lotes vencidos --}}
    <div class="flex items-center gap-5 rounded-2xl border px-6 py-5 shadow-sm
        {{ $expiredCount > 0 ? 'border-red-100 bg-red-50 dark:border-red-900/40 dark:bg-red-950/20' : 'border-gray-100 bg-white dark:border-gray-700 dark:bg-gray-800' }}">
        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl
            {{ $expiredCount > 0 ? 'bg-red-100 dark:bg-red-900/40' : 'bg-gray-100 dark:bg-gray-700' }}">
            <svg class="h-6 w-6 {{ $expiredCount > 0 ? 'text-[#e11d48]' : 'text-gray-400' }}"
                fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </div>
        <div class="flex-1">
            <p class="text-[10px] font-bold uppercase tracking-widest {{ $expiredCount > 0 ? 'text-[#e11d48]' : 'text-gray-400' }}">
                Lotes vencidos
            </p>
            <p class="mt-0.5 font-mono text-2xl font-black {{ $expiredCount > 0 ? 'text-[#e11d48]' : 'text-gray-500 dark:text-gray-300' }}">
                {{ $expiredCount }} <span class="text-sm font-medium">con stock</span>
            </p>
        </div>
        @if($expiredCount > 0)
            <a href="{{ route('reports.expirations', ['days' => 0]) }}"
                class="shrink-0 rounded-xl bg-[#e11d48] px-4 py-2 text-xs font-bold text-white transition-all hover:bg-[#be123c]">
                Ver ahora
            </a>
        @else
            <span class="shrink-0 rounded-xl border border-gray-200 px-4 py-2 text-xs font-bold text-gray-400 dark:border-gray-600">
                Sin alertas
            </span>
        @endif
    </div>
</div>

{{-- ── Últimas ventas + Accesos rápidos ─────────────────────────────────────────── --}}
<div class="grid gap-4 lg:grid-cols-3">

    {{-- Últimas ventas --}}
    <div class="lg:col-span-2 rounded-2xl border border-gray-100 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
        <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4 dark:border-gray-700">
            <h2 class="font-bold text-[#1e293b] dark:text-white">Últimas ventas</h2>
            <a href="{{ route('sales.index') }}" class="text-xs font-bold text-[#e11d48] hover:underline">Ver todas →</a>
        </div>

        @if($recentSales->isEmpty())
            <div class="flex flex-col items-center justify-center gap-3 py-14 text-center">
                <div class="flex h-14 w-14 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-700">
                    <svg class="h-7 w-7 text-gray-300 dark:text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
                <p class="text-sm font-medium text-gray-400">No hay ventas registradas aún</p>
                <a href="{{ route('sales.create') }}"
                    class="rounded-xl bg-[#e11d48] px-5 py-2 text-sm font-bold text-white hover:bg-[#be123c]">
                    Registrar venta
                </a>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-50 dark:border-gray-700/50">
                            <th class="px-6 py-3 text-left text-[10px] font-bold uppercase tracking-widest text-gray-400">Referencia</th>
                            <th class="px-4 py-3 text-left text-[10px] font-bold uppercase tracking-widest text-gray-400">Cliente</th>
                            <th class="px-4 py-3 text-left text-[10px] font-bold uppercase tracking-widest text-gray-400">Total</th>
                            <th class="px-4 py-3 text-left text-[10px] font-bold uppercase tracking-widest text-gray-400">Estado</th>
                            <th class="px-4 py-3 text-left text-[10px] font-bold uppercase tracking-widest text-gray-400">Hora</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 dark:divide-gray-700/50">
                        @foreach($recentSales as $sale)
                            <tr class="transition-colors hover:bg-gray-50/50 dark:hover:bg-gray-700/30">
                                <td class="px-6 py-3.5">
                                    <span class="font-mono text-xs font-bold text-[#1e293b] dark:text-white">
                                        {{ $sale->reference_doc ?? '—' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3.5 text-gray-500 dark:text-gray-400">
                                    {{ $sale->document?->client_name ?? 'Sin nombre' }}
                                </td>
                                <td class="px-4 py-3.5">
                                    <span class="font-bold text-[#1e293b] dark:text-white">
                                        @if($sale->document?->total_amount)
                                            Bs. {{ number_format($sale->document->total_amount, 2) }}
                                        @else
                                            —
                                        @endif
                                    </span>
                                </td>
                                <td class="px-4 py-3.5">
                                    @php $status = $sale->document?->status ?? 'open'; @endphp
                                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wide
                                        {{ $status === 'closed'
                                            ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-400'
                                            : ($status === 'cancelled'
                                                ? 'bg-red-100 text-red-600 dark:bg-red-900/40 dark:text-red-400'
                                                : 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-400') }}">
                                        {{ $status === 'closed' ? 'Cerrado' : ($status === 'cancelled' ? 'Anulado' : 'Abierto') }}
                                    </span>
                                </td>
                                <td class="px-4 py-3.5 text-xs text-gray-400">
                                    {{ $sale->created_at->format('H:i') }}
                                    @if($sale->created_at->isToday())
                                        <span class="ml-1 inline-flex h-1.5 w-1.5 rounded-full bg-emerald-400 align-middle"></span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    {{-- Accesos rápidos --}}
    <div class="flex flex-col gap-3">
        <h2 class="font-bold text-[#1e293b] dark:text-white">Accesos rápidos</h2>

        <a href="{{ route('reports.expirations') }}"
            class="group flex items-center gap-4 rounded-2xl border border-gray-100 bg-white p-4 shadow-sm transition-all hover:border-amber-200 hover:bg-amber-50/50 dark:border-gray-700 dark:bg-gray-800">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-amber-100 text-amber-600 dark:bg-amber-900/40 dark:text-amber-400">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </div>
            <div>
                <p class="text-sm font-bold text-[#1e293b] group-hover:text-amber-700 dark:text-white">Vencimientos</p>
                <p class="text-xs text-gray-400">Alertas de lotes próximos a vencer</p>
            </div>
        </a>

        <a href="{{ route('reports.stock') }}"
            class="group flex items-center gap-4 rounded-2xl border border-gray-100 bg-white p-4 shadow-sm transition-all hover:border-blue-200 hover:bg-blue-50/50 dark:border-gray-700 dark:bg-gray-800">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-100 text-blue-600 dark:bg-blue-900/40 dark:text-blue-400">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
            </div>
            <div>
                <p class="text-sm font-bold text-[#1e293b] group-hover:text-blue-700 dark:text-white">Stock Actual</p>
                <p class="text-xs text-gray-400">Valorización por ubicación</p>
            </div>
        </a>

        <a href="{{ route('reports.movements') }}"
            class="group flex items-center gap-4 rounded-2xl border border-gray-100 bg-white p-4 shadow-sm transition-all hover:border-emerald-200 hover:bg-emerald-50/50 dark:border-gray-700 dark:bg-gray-800">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-emerald-100 text-emerald-600 dark:bg-emerald-900/40 dark:text-emerald-400">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                </svg>
            </div>
            <div>
                <p class="text-sm font-bold text-[#1e293b] group-hover:text-emerald-700 dark:text-white">Kardex</p>
                <p class="text-xs text-gray-400">Historial de movimientos</p>
            </div>
        </a>

        <a href="{{ route('products.create') }}"
            class="group flex items-center gap-4 rounded-2xl border border-gray-100 bg-white p-4 shadow-sm transition-all hover:border-red-200 hover:bg-red-50/50 dark:border-gray-700 dark:bg-gray-800">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-red-100 text-[#e11d48] dark:bg-red-900/40">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
            </div>
            <div>
                <p class="text-sm font-bold text-[#1e293b] group-hover:text-[#e11d48] dark:text-white">Nuevo Producto</p>
                <p class="text-xs text-gray-400">Agregar al catálogo</p>
            </div>
        </a>

        <a href="{{ route('suppliers.index') }}"
            class="group flex items-center gap-4 rounded-2xl border border-gray-100 bg-white p-4 shadow-sm transition-all hover:border-purple-200 hover:bg-purple-50/50 dark:border-gray-700 dark:bg-gray-800">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-purple-100 text-purple-600 dark:bg-purple-900/40 dark:text-purple-400">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </div>
            <div>
                <p class="text-sm font-bold text-[#1e293b] group-hover:text-purple-700 dark:text-white">Proveedores</p>
                <p class="text-xs text-gray-400">Gestionar proveedores</p>
            </div>
        </a>
    </div>
</div>
@endsection
            <svg class="mt-0.5 h-5 w-5 shrink-0 text-[#e11d48]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
            </svg>
            <div class="flex-1">
                <p class="font-bold text-[#e11d48]">
                    Alerta crítica: {{ $expiredCount }} {{ Str::plural('lote', $expiredCount) }} vencido{{ $expiredCount !== 1 ? 's' : '' }} con stock disponible
                </p>
                <p class="mt-0.5 text-sm font-medium text-red-500">
                    Estos productos no deben venderse. Revise el reporte de vencimientos inmediatamente.
                </p>
            </div>
            <a href="{{ route('reports.expirations', ['days' => 0]) }}"
                class="shrink-0 rounded-xl bg-[#e11d48] px-5 py-2 text-sm font-bold text-white hover:bg-[#be123c] transition-all">
                Ver vencidos
            </a>
        </div>
    @endif

    {{-- ── Tarjetas de resumen ── --}}
    <div class="mb-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">

        {{-- Vencen en 30 días --}}
        <div class="rounded-[2rem] border border-amber-100 bg-amber-50 p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <p class="text-[10px] font-bold uppercase tracking-widest text-amber-600">Vencen en 30 días</p>
                <svg class="h-5 w-5 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <p class="mt-3 font-mono text-3xl font-black text-amber-700">{{ $expiringCount }}</p>
            <p class="mt-1 text-xs font-medium text-amber-500">lotes con stock</p>
            <a href="{{ route('reports.expirations') }}" class="mt-3 block text-xs font-bold text-amber-600 hover:underline">Ver reporte →</a>
        </div>

        {{-- Lotes vencidos --}}
        <div class="rounded-[2rem] border p-6 shadow-sm {{ $expiredCount > 0 ? 'border-red-100 bg-red-50' : 'border-gray-100 bg-white' }}">
            <div class="flex items-center justify-between">
                <p class="text-[10px] font-bold uppercase tracking-widest {{ $expiredCount > 0 ? 'text-[#e11d48]' : 'text-gray-400' }}">
                    Lotes vencidos
                </p>
                <svg class="h-5 w-5 {{ $expiredCount > 0 ? 'text-[#e11d48]' : 'text-gray-300' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </div>
            <p class="mt-3 font-mono text-3xl font-black {{ $expiredCount > 0 ? 'text-[#e11d48]' : 'text-gray-600' }}">
                {{ $expiredCount }}
            </p>
            <p class="mt-1 text-xs font-medium {{ $expiredCount > 0 ? 'text-red-400' : 'text-gray-400' }}">con stock disponible</p>
            @if($expiredCount > 0)
                <a href="{{ route('reports.expirations', ['days' => 0]) }}" class="mt-3 block text-xs font-bold text-[#e11d48] hover:underline">Ver vencidos →</a>
            @endif
        </div>

        {{-- Productos activos --}}
        <div class="rounded-[2rem] border border-gray-100 bg-white p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400">Productos activos</p>
                <svg class="h-5 w-5 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10"/>
                </svg>
            </div>
            <p class="mt-3 font-mono text-3xl font-black text-[#1e293b]">{{ $activeProducts }}</p>
            <p class="mt-1 text-xs font-medium text-gray-400">en catálogo</p>
            <a href="{{ route('reports.stock') }}" class="mt-3 block text-xs font-bold text-blue-500 hover:underline">Ver stock →</a>
        </div>

        {{-- Movimientos hoy --}}
        <div class="rounded-[2rem] border border-gray-100 bg-white p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400">Movimientos hoy</p>
                <svg class="h-5 w-5 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
            </div>
            <p class="mt-3 font-mono text-3xl font-black text-[#1e293b]">{{ $todayMovements }}</p>
            <p class="mt-1 text-xs font-medium text-gray-400">registros de inventario</p>
            <a href="{{ route('reports.movements', ['date_from' => today()->toDateString(), 'date_to' => today()->toDateString()]) }}"
                class="mt-3 block text-xs font-bold text-blue-500 hover:underline">Ver movimientos →</a>
        </div>
    </div>

    {{-- ── Accesos rápidos ── --}}
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">

        <a href="{{ route('reports.expirations') }}"
            class="group flex flex-col gap-3 rounded-[2rem] border border-gray-100 bg-white p-6 shadow-sm transition-all hover:border-amber-200 hover:bg-amber-50/50 hover:shadow-md">
            <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-amber-100 text-amber-600">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </div>
            <div>
                <p class="font-bold text-[#1e293b] group-hover:text-amber-700">Vencimientos</p>
                <p class="mt-0.5 text-xs text-gray-400">Alertas de lotes próximos a vencer</p>
            </div>
        </a>

        <a href="{{ route('reports.stock') }}"
            class="group flex flex-col gap-3 rounded-[2rem] border border-gray-100 bg-white p-6 shadow-sm transition-all hover:border-blue-200 hover:bg-blue-50/50 hover:shadow-md">
            <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-blue-100 text-blue-600">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
            </div>
            <div>
                <p class="font-bold text-[#1e293b] group-hover:text-blue-700">Stock Actual</p>
                <p class="mt-0.5 text-xs text-gray-400">Valorización de inventario por ubicación</p>
            </div>
        </a>

        <a href="{{ route('reports.movements') }}"
            class="group flex flex-col gap-3 rounded-[2rem] border border-gray-100 bg-white p-6 shadow-sm transition-all hover:border-emerald-200 hover:bg-emerald-50/50 hover:shadow-md">
            <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-emerald-100 text-emerald-600">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                </svg>
            </div>
            <div>
                <p class="font-bold text-[#1e293b] group-hover:text-emerald-700">Kardex</p>
                <p class="mt-0.5 text-xs text-gray-400">Historial completo de movimientos</p>
            </div>
        </a>

        <a href="{{ route('sales.index') }}"
            class="group flex flex-col gap-3 rounded-[2rem] border border-gray-100 bg-white p-6 shadow-sm transition-all hover:border-purple-200 hover:bg-purple-50/50 hover:shadow-md">
            <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-purple-100 text-purple-600">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
            </div>
            <div>
                <p class="font-bold text-[#1e293b] group-hover:text-purple-700">Ventas</p>
                <p class="mt-0.5 text-xs text-gray-400">Historial de ventas y recibos</p>
            </div>
        </a>
    </div>
@endsection