@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Vencimientos de Lotes" />

    {{-- Filters --}}
    <form method="GET" action="{{ route('reports.expirations') }}"
          class="mb-5 flex flex-wrap items-end gap-3 rounded-2xl border border-gray-200 bg-white px-5 py-4 dark:border-gray-800 dark:bg-white/[0.03]">

        {{-- Days preset --}}
        <div>
            <label class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400">Vencen en</label>
            <div x-data="{ isOptionSelected: true }" class="relative z-20">
                <select name="days" @change="isOptionSelected = true"
                    class="shadow-theme-xs h-10 w-36 appearance-none rounded-lg border border-gray-300 bg-transparent px-3 pr-8 text-sm text-gray-800 focus:border-brand-300 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                    <option value="0"   {{ $days == 0  ? 'selected' : '' }}>Solo vencidos</option>
                    <option value="15"  {{ $days == 15 ? 'selected' : '' }}>15 días</option>
                    <option value="30"  {{ $days == 30 ? 'selected' : '' }}>30 días</option>
                    <option value="60"  {{ $days == 60 ? 'selected' : '' }}>60 días</option>
                    <option value="90"  {{ $days == 90 ? 'selected' : '' }}>90 días</option>
                </select>
                <span class="pointer-events-none absolute top-1/2 right-2.5 z-30 -translate-y-1/2 text-gray-400">
                    <svg class="stroke-current" width="16" height="16" viewBox="0 0 20 20" fill="none"><path d="M4.79175 7.396L10.0001 12.6043L15.2084 7.396" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </span>
            </div>
        </div>

        {{-- Location --}}
        <div>
            <label class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400">Ubicación</label>
            <div x-data="{ isOptionSelected: {{ $locationId ? 'true' : 'false' }} }" class="relative z-20">
                <select name="location_id" @change="isOptionSelected = true"
                    class="shadow-theme-xs h-10 w-44 appearance-none rounded-lg border border-gray-300 bg-transparent px-3 pr-8 text-sm focus:border-brand-300 focus:outline-none dark:border-gray-700 dark:bg-gray-900"
                    :class="isOptionSelected ? 'text-gray-800 dark:text-white/90' : 'text-gray-400 dark:text-gray-500'">
                    <option value="">Todas</option>
                    @foreach($locations as $loc)
                        <option value="{{ $loc->id }}" {{ $locationId == $loc->id ? 'selected' : '' }}
                            class="text-gray-700 dark:bg-gray-900 dark:text-gray-400">{{ $loc->name }}</option>
                    @endforeach
                </select>
                <span class="pointer-events-none absolute top-1/2 right-2.5 z-30 -translate-y-1/2 text-gray-400">
                    <svg class="stroke-current" width="16" height="16" viewBox="0 0 20 20" fill="none"><path d="M4.79175 7.396L10.0001 12.6043L15.2084 7.396" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </span>
            </div>
        </div>

        <button type="submit"
            class="h-10 rounded-lg bg-brand-500 px-5 text-sm font-medium text-white hover:bg-brand-600">
            Filtrar
        </button>
        <a href="{{ route('reports.expirations') }}"
            class="inline-flex h-10 items-center rounded-lg bg-gray-100 px-4 text-sm font-medium text-gray-600 hover:bg-gray-200 dark:bg-white/10 dark:text-white/80 dark:hover:bg-white/20">
            Limpiar
        </a>

        {{-- Export --}}
        <a href="{{ route('reports.expirations', array_merge(request()->query(), ['export' => 1])) }}"
            class="ml-auto inline-flex h-10 items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 text-sm font-medium text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:bg-white/[0.03] dark:text-gray-400 dark:hover:bg-white/[0.06]">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
            </svg>
            Exportar CSV
        </a>
    </form>

    {{-- Legend --}}
    <div class="mb-4 flex flex-wrap items-center gap-3 text-xs">
        <span class="font-medium text-gray-500 dark:text-gray-400">Urgencia:</span>
        <span class="inline-flex items-center gap-1.5 rounded-full bg-error-50 px-2.5 py-0.5 font-medium text-error-600 dark:bg-error-500/10 dark:text-error-400">
            <span class="h-1.5 w-1.5 rounded-full bg-error-500"></span>Vencido
        </span>
        <span class="inline-flex items-center gap-1.5 rounded-full bg-orange-50 px-2.5 py-0.5 font-medium text-orange-600 dark:bg-orange-500/10 dark:text-orange-400">
            <span class="h-1.5 w-1.5 rounded-full bg-orange-500"></span>≤ 15 días
        </span>
        <span class="inline-flex items-center gap-1.5 rounded-full bg-warning-50 px-2.5 py-0.5 font-medium text-warning-600 dark:bg-warning-500/10 dark:text-warning-400">
            <span class="h-1.5 w-1.5 rounded-full bg-warning-500"></span>≤ 30 días
        </span>
        <span class="inline-flex items-center gap-1.5 rounded-full bg-success-50 px-2.5 py-0.5 font-medium text-success-600 dark:bg-success-500/10 dark:text-success-400">
            <span class="h-1.5 w-1.5 rounded-full bg-success-500"></span>≤ 60 días
        </span>
    </div>

    {{-- Table --}}
    <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="border-b border-gray-100 px-5 py-3.5 dark:border-gray-800">
            <p class="text-sm font-medium text-gray-700 dark:text-gray-300">
                {{ $batches->count() }} {{ Str::plural('lote', $batches->count()) }} encontrado{{ $batches->count() !== 1 ? 's' : '' }}
                @if($days > 0)
                    que vencen dentro de <strong>{{ $days }} días</strong>
                @else
                    <span class="text-error-600 dark:text-error-400 font-semibold">ya vencidos</span>
                @endif
            </p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 dark:border-gray-800">
                        <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Urgencia</th>
                        <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Lote</th>
                        <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Producto</th>
                        <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Ubicación</th>
                        <th class="px-5 py-3 text-center text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Vencimiento</th>
                        <th class="px-5 py-3 text-right text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Stock</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($batches as $row)
                        @php
                            $exp  = \Carbon\Carbon::parse($row->expiration_date);
                            $diff = now()->startOfDay()->diffInDays($exp->startOfDay(), false);

                            if ($diff < 0) {
                                $badge = 'bg-error-50 text-error-600 dark:bg-error-500/10 dark:text-error-400';
                                $label = 'Vencido';
                            } elseif ($diff <= 15) {
                                $badge = 'bg-orange-50 text-orange-600 dark:bg-orange-500/10 dark:text-orange-400';
                                $label = "En {$diff}d";
                            } elseif ($diff <= 30) {
                                $badge = 'bg-warning-50 text-warning-600 dark:bg-warning-500/10 dark:text-warning-400';
                                $label = "En {$diff}d";
                            } else {
                                $badge = 'bg-success-50 text-success-600 dark:bg-success-500/10 dark:text-success-400';
                                $label = "En {$diff}d";
                            }
                        @endphp
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-white/[0.02]">
                            <td class="px-5 py-3">
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $badge }}">
                                    {{ $label }}
                                </span>
                            </td>
                            <td class="px-5 py-3 font-mono text-xs text-gray-700 dark:text-gray-300">
                                {{ $row->batch_code }}
                            </td>
                            <td class="px-5 py-3 font-medium text-gray-800 dark:text-white/90">
                                {{ $row->product }}
                            </td>
                            <td class="px-5 py-3 text-gray-600 dark:text-gray-400">
                                {{ $row->location }}
                            </td>
                            <td class="px-5 py-3 text-center font-mono text-sm {{ $diff < 0 ? 'text-error-600 font-semibold dark:text-error-400' : 'text-gray-600 dark:text-gray-400' }}">
                                {{ $exp->format('d/m/Y') }}
                            </td>
                            <td class="px-5 py-3 text-right font-medium text-gray-800 dark:text-white/90">
                                {{ number_format($row->quantity, 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-12 text-center text-sm text-gray-400 dark:text-gray-500">
                                @if($days == 0)
                                    No hay lotes vencidos con stock. ✓
                                @else
                                    No hay lotes que vencen en los próximos {{ $days }} días.
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4 print:hidden">
        <a href="{{ route('reports.index') }}"
            class="rounded-lg bg-gray-100 px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-200 dark:bg-white/10 dark:text-white/80 dark:hover:bg-white/20">
            ← Volver a Reportes
        </a>
    </div>
@endsection
