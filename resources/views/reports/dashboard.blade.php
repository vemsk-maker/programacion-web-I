@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Panel de Reportes" />

    {{-- Alerta crítica: lotes vencidos con stock --}}
    @if($expiredCount > 0)
        <div class="mb-6 flex items-start gap-4 rounded-2xl border border-red-100 bg-red-50 px-6 py-5">
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