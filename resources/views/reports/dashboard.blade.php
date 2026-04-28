@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Panel de Reportes" />

    {{-- ── Critical alert: expired batches with stock ── --}}
    @if($expiredCount > 0)
        <div class="mb-5 flex items-start gap-3 rounded-2xl border border-error-200 bg-error-50 px-5 py-4 dark:border-error-500/20 dark:bg-error-500/10">
            <svg class="mt-0.5 h-5 w-5 shrink-0 text-error-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
            </svg>
            <div class="flex-1">
                <p class="font-semibold text-error-700 dark:text-error-400">
                    ⚠ Alerta crítica: {{ $expiredCount }} {{ Str::plural('lote', $expiredCount) }} vencido{{ $expiredCount !== 1 ? 's' : '' }} con stock disponible
                </p>
                <p class="mt-0.5 text-sm text-error-600 dark:text-error-400">
                    Estos productos no deben venderse. Revise el reporte de vencimientos inmediatamente.
                </p>
            </div>
            <a href="{{ route('reports.expirations', ['days' => 0]) }}"
                class="shrink-0 rounded-lg bg-error-600 px-4 py-2 text-sm font-medium text-white hover:bg-error-700">
                Ver vencidos
            </a>
        </div>
    @endif

    {{-- ── Summary cards ── --}}
    <div class="mb-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">

        {{-- Expiring in 30 days --}}
        <div class="rounded-2xl border border-warning-200 bg-warning-50 p-5 dark:border-warning-500/20 dark:bg-warning-500/10">
            <div class="flex items-center justify-between">
                <p class="text-xs font-medium uppercase tracking-wide text-warning-600 dark:text-warning-400">Vencen en 30 días</p>
                <svg class="h-5 w-5 text-warning-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <p class="mt-2 text-3xl font-bold text-warning-700 dark:text-warning-300">{{ $expiringCount }}</p>
            <p class="mt-1 text-xs text-warning-500 dark:text-warning-500">lotes con stock</p>
            <a href="{{ route('reports.expirations') }}" class="mt-3 block text-xs font-medium text-warning-600 hover:underline dark:text-warning-400">Ver reporte →</a>
        </div>

        {{-- Expired --}}
        <div class="rounded-2xl border p-5 {{ $expiredCount > 0 ? 'border-error-200 bg-error-50 dark:border-error-500/20 dark:bg-error-500/10' : 'border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]' }}">
            <div class="flex items-center justify-between">
                <p class="text-xs font-medium uppercase tracking-wide {{ $expiredCount > 0 ? 'text-error-600 dark:text-error-400' : 'text-gray-400 dark:text-gray-500' }}">
                    Lotes vencidos
                </p>
                <svg class="h-5 w-5 {{ $expiredCount > 0 ? 'text-error-500' : 'text-gray-300 dark:text-gray-600' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </div>
            <p class="mt-2 text-3xl font-bold {{ $expiredCount > 0 ? 'text-error-700 dark:text-error-300' : 'text-gray-600 dark:text-gray-300' }}">
                {{ $expiredCount }}
            </p>
            <p class="mt-1 text-xs {{ $expiredCount > 0 ? 'text-error-500' : 'text-gray-400 dark:text-gray-500' }}">con stock disponible</p>
            @if($expiredCount > 0)
                <a href="{{ route('reports.expirations', ['days' => 0]) }}" class="mt-3 block text-xs font-medium text-error-600 hover:underline dark:text-error-400">Ver vencidos →</a>
            @endif
        </div>

        {{-- Active products --}}
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-center justify-between">
                <p class="text-xs font-medium uppercase tracking-wide text-gray-400 dark:text-gray-500">Productos activos</p>
                <svg class="h-5 w-5 text-gray-300 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10"/>
                </svg>
            </div>
            <p class="mt-2 text-3xl font-bold text-gray-800 dark:text-white/90">{{ $activeProducts }}</p>
            <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">en catálogo</p>
            <a href="{{ route('reports.stock') }}" class="mt-3 block text-xs font-medium text-brand-600 hover:underline dark:text-brand-400">Ver stock →</a>
        </div>

        {{-- Today movements --}}
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-center justify-between">
                <p class="text-xs font-medium uppercase tracking-wide text-gray-400 dark:text-gray-500">Movimientos hoy</p>
                <svg class="h-5 w-5 text-gray-300 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
            </div>
            <p class="mt-2 text-3xl font-bold text-gray-800 dark:text-white/90">{{ $todayMovements }}</p>
            <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">registros de inventario</p>
            <a href="{{ route('reports.movements', ['date_from' => today()->toDateString(), 'date_to' => today()->toDateString()]) }}"
                class="mt-3 block text-xs font-medium text-brand-600 hover:underline dark:text-brand-400">Ver movimientos →</a>
        </div>
    </div>

    {{-- ── Quick links grid ── --}}
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">

        <a href="{{ route('reports.expirations') }}"
            class="group flex flex-col gap-2 rounded-2xl border border-gray-200 bg-white p-5 transition hover:border-warning-300 hover:bg-warning-50/50 dark:border-gray-800 dark:bg-white/[0.03] dark:hover:border-warning-500/30 dark:hover:bg-warning-500/5">
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-warning-100 text-warning-600 dark:bg-warning-500/10 dark:text-warning-400">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </div>
            <p class="font-semibold text-gray-800 group-hover:text-warning-700 dark:text-white/90 dark:group-hover:text-warning-400">Vencimientos</p>
            <p class="text-xs text-gray-400 dark:text-gray-500">Alertas de lotes próximos a vencer</p>
        </a>

        <a href="{{ route('reports.stock') }}"
            class="group flex flex-col gap-2 rounded-2xl border border-gray-200 bg-white p-5 transition hover:border-brand-300 hover:bg-brand-50/50 dark:border-gray-800 dark:bg-white/[0.03] dark:hover:border-brand-500/30 dark:hover:bg-brand-500/5">
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-brand-100 text-brand-600 dark:bg-brand-500/10 dark:text-brand-400">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
            </div>
            <p class="font-semibold text-gray-800 group-hover:text-brand-700 dark:text-white/90 dark:group-hover:text-brand-400">Stock Actual</p>
            <p class="text-xs text-gray-400 dark:text-gray-500">Valorización de inventario por ubicación</p>
        </a>

        <a href="{{ route('reports.movements') }}"
            class="group flex flex-col gap-2 rounded-2xl border border-gray-200 bg-white p-5 transition hover:border-success-300 hover:bg-success-50/50 dark:border-gray-800 dark:bg-white/[0.03] dark:hover:border-success-500/30 dark:hover:bg-success-500/5">
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-success-100 text-success-600 dark:bg-success-500/10 dark:text-success-400">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                </svg>
            </div>
            <p class="font-semibold text-gray-800 group-hover:text-success-700 dark:text-white/90 dark:group-hover:text-success-400">Kardex</p>
            <p class="text-xs text-gray-400 dark:text-gray-500">Historial completo de movimientos</p>
        </a>

        <a href="{{ route('sales.index') }}"
            class="group flex flex-col gap-2 rounded-2xl border border-gray-200 bg-white p-5 transition hover:border-purple-300 hover:bg-purple-50/50 dark:border-gray-800 dark:bg-white/[0.03] dark:hover:border-purple-500/30 dark:hover:bg-purple-500/5">
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-purple-100 text-purple-600 dark:bg-purple-500/10 dark:text-purple-400">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
            </div>
            <p class="font-semibold text-gray-800 group-hover:text-purple-700 dark:text-white/90 dark:group-hover:text-purple-400">Ventas</p>
            <p class="text-xs text-gray-400 dark:text-gray-500">Historial de ventas y recibos</p>
        </a>
    </div>
@endsection
