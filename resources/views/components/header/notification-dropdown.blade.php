{{-- Notification Dropdown Component --}}
@php
    use Carbon\Carbon;

    $today = Carbon::today();

    // Lotes vencidos con stock > 0
    $expiredBatches = \App\Models\Batch::with('product')
        ->whereNotNull('expiration_date')
        ->where('expiration_date', '<', $today)
        ->whereHas('stockCache', fn($q) => $q->where('quantity', '>', 0))
        ->orderBy('expiration_date')
        ->limit(5)
        ->get();

    // Lotes que vencen en los próximos 60 días
    $expiringBatches = \App\Models\Batch::with('product')
        ->whereNotNull('expiration_date')
        ->whereBetween('expiration_date', [$today, $today->copy()->addDays(60)])
        ->orderBy('expiration_date')
        ->limit(10)
        ->get();

    $totalAlerts = $expiredBatches->count() + $expiringBatches->count();
@endphp
<div class="relative" x-data="{
    dropdownOpen: false,
    notifying: {{ $totalAlerts > 0 ? 'true' : 'false' }},
    toggleDropdown() {
        this.dropdownOpen = !this.dropdownOpen;
        this.notifying = false;
    },
    closeDropdown() {
        this.dropdownOpen = false;
    }
}" @click.away="closeDropdown()">
    <!-- Notification Button -->
    <button
        class="relative flex items-center justify-center text-gray-500 transition-colors bg-white border border-gray-200 rounded-full hover:text-dark-900 h-11 w-11 hover:bg-gray-100 hover:text-gray-700 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-white"
        @click="toggleDropdown()"
        type="button"
    >
        <!-- Notification Badge -->
        <span
            x-show="notifying"
            class="absolute right-0 top-0.5 z-1 h-2 w-2 rounded-full bg-orange-400"
        >
            <span class="absolute inline-flex w-full h-full bg-orange-400 rounded-full opacity-75 -z-1 animate-ping"></span>
        </span>

        <!-- Bell Icon -->
        <svg class="fill-current" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path fill-rule="evenodd" clip-rule="evenodd"
                d="M10.75 2.29248C10.75 1.87827 10.4143 1.54248 10 1.54248C9.58583 1.54248 9.25004 1.87827 9.25004 2.29248V2.83613C6.08266 3.20733 3.62504 5.9004 3.62504 9.16748V14.4591H3.33337C2.91916 14.4591 2.58337 14.7949 2.58337 15.2091C2.58337 15.6234 2.91916 15.9591 3.33337 15.9591H4.37504H15.625H16.6667C17.0809 15.9591 17.4167 15.6234 17.4167 15.2091C17.4167 14.7949 17.0809 14.4591 16.6667 14.4591H16.375V9.16748C16.375 5.9004 13.9174 3.20733 10.75 2.83613V2.29248ZM14.875 14.4591V9.16748C14.875 6.47509 12.6924 4.29248 10 4.29248C7.30765 4.29248 5.12504 6.47509 5.12504 9.16748V14.4591H14.875ZM8.00004 17.7085C8.00004 18.1228 8.33583 18.4585 8.75004 18.4585H11.25C11.6643 18.4585 12 18.1228 12 17.7085C12 17.2943 11.6643 16.9585 11.25 16.9585H8.75004C8.33583 16.9585 8.00004 17.2943 8.00004 17.7085Z"
                fill="" />
        </svg>
    </button>

    <!-- Dropdown Start -->
    <div
        x-show="dropdownOpen"
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="transform opacity-0 scale-95"
        x-transition:enter-end="transform opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="transform opacity-100 scale-100"
        x-transition:leave-end="transform opacity-0 scale-95"
        class="absolute -right-[240px] mt-[17px] flex h-[480px] w-[350px] flex-col rounded-2xl border border-gray-200 bg-white p-3 shadow-theme-lg dark:border-gray-800 dark:bg-gray-dark sm:w-[361px] lg:right-0"
        style="display: none;"
    >
        <!-- Header -->
        <div class="flex items-center justify-between pb-3 mb-3 border-b border-gray-100 dark:border-gray-800">
            <div class="flex items-center gap-2">
                <h5 class="text-lg font-semibold text-gray-800 dark:text-white/90">Notificaciones</h5>
                @if($totalAlerts > 0)
                    <span class="flex h-5 min-w-5 items-center justify-center rounded-full bg-[#e11d48] px-1 text-[10px] font-bold text-white">
                        {{ $totalAlerts }}
                    </span>
                @endif
            </div>
            <button @click="closeDropdown()" class="text-gray-500 dark:text-gray-400" type="button">
                <svg class="fill-current" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd" clip-rule="evenodd"
                        d="M6.21967 7.28131C5.92678 6.98841 5.92678 6.51354 6.21967 6.22065C6.51256 5.92775 6.98744 5.92775 7.28033 6.22065L11.999 10.9393L16.7176 6.22078C17.0105 5.92789 17.4854 5.92788 17.7782 6.22078C18.0711 6.51367 18.0711 6.98855 17.7782 7.28144L13.0597 12L17.7782 16.7186C18.0711 17.0115 18.0711 17.4863 17.7782 17.7792C17.4854 18.0721 17.0105 18.0721 16.7176 17.7792L11.999 13.0607L7.28033 17.7794C6.98744 18.0722 6.51256 18.0722 6.21967 17.7794C5.92678 17.4865 5.92678 17.0116 6.21967 16.7187L10.9384 12L6.21967 7.28131Z"
                        fill="" />
                </svg>
            </button>
        </div>

        <!-- Notification List -->
        <ul class="flex flex-col h-auto overflow-y-auto custom-scrollbar">

            @if($totalAlerts === 0)
                <li class="flex flex-col items-center justify-center py-12 text-center">
                    <div class="mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-green-50 text-green-500">
                        <svg width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <p class="text-sm font-semibold text-gray-700 dark:text-gray-300">Sin alertas de vencimiento</p>
                    <p class="mt-1 text-xs text-gray-400">Todos los lotes están dentro del rango</p>
                </li>
            @endif

            {{-- ── Lotes vencidos ─────────────────────────────── --}}
            @foreach($expiredBatches as $batch)
                @php $daysAgo = abs($batch->expiration_date->diffInDays($today)); @endphp
                <li>
                    <a href="{{ route('reports.expirations') }}"
                        class="flex gap-3 rounded-lg border-b border-gray-100 p-3 hover:bg-red-50 dark:border-gray-800 dark:hover:bg-white/5">
                        <!-- Ícono rojo: vencido -->
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-red-100 text-[#e11d48]">
                            <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                            </svg>
                        </span>
                        <span class="block min-w-0 flex-1">
                            <span class="mb-1 block text-sm font-semibold text-[#e11d48]">
                                Lote vencido
                            </span>
                            <span class="block truncate text-sm text-gray-700 dark:text-gray-300">
                                {{ $batch->product->name ?? 'Producto desconocido' }}
                            </span>
                            <span class="mt-1 flex items-center gap-1.5 text-xs text-gray-400">
                                <span class="h-1.5 w-1.5 rounded-full bg-red-400"></span>
                                Venció hace {{ $daysAgo }} {{ $daysAgo === 1 ? 'día' : 'días' }}
                                @if($batch->batch_code)
                                    · Lote: {{ $batch->batch_code }}
                                @endif
                            </span>
                        </span>
                    </a>
                </li>
            @endforeach

            {{-- ── Lotes por vencer ────────────────────────────── --}}
            @foreach($expiringBatches as $batch)
                @php
                    $daysLeft = $today->diffInDays($batch->expiration_date);
                    if ($daysLeft <= 7) {
                        $color = 'text-[#e11d48]';
                        $bg    = 'bg-red-100';
                        $dot   = 'bg-red-400';
                        $label = 'Vence hoy';
                        if ($daysLeft > 0) $label = 'Vence en ' . $daysLeft . ' ' . ($daysLeft === 1 ? 'día' : 'días');
                    } elseif ($daysLeft <= 30) {
                        $color = 'text-orange-600';
                        $bg    = 'bg-orange-100';
                        $dot   = 'bg-orange-400';
                        $label = 'Vence en ' . $daysLeft . ' días';
                    } else {
                        $color = 'text-yellow-600';
                        $bg    = 'bg-yellow-100';
                        $dot   = 'bg-yellow-400';
                        $label = 'Vence en ' . $daysLeft . ' días';
                    }
                @endphp
                <li>
                    <a href="{{ route('reports.expirations') }}"
                        class="flex gap-3 rounded-lg border-b border-gray-100 p-3 hover:bg-gray-50 dark:border-gray-800 dark:hover:bg-white/5">
                        <!-- Ícono según urgencia -->
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full {{ $bg }} {{ $color }}">
                            <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </span>
                        <span class="block min-w-0 flex-1">
                            <span class="mb-1 block text-sm font-semibold {{ $color }}">
                                {{ $label }}
                            </span>
                            <span class="block truncate text-sm text-gray-700 dark:text-gray-300">
                                {{ $batch->product->name ?? 'Producto desconocido' }}
                            </span>
                            <span class="mt-1 flex items-center gap-1.5 text-xs text-gray-400">
                                <span class="h-1.5 w-1.5 rounded-full {{ $dot }}"></span>
                                {{ $batch->expiration_date->format('d/m/Y') }}
                                @if($batch->batch_code)
                                    · Lote: {{ $batch->batch_code }}
                                @endif
                            </span>
                        </span>
                    </a>
                </li>
            @endforeach

        </ul>

        <!-- Ver reporte -->
        <a
            href="{{ route('reports.expirations') }}"
            class="mt-3 flex justify-center rounded-lg border border-gray-300 bg-white p-3 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200"
        >
            Ver reporte de vencimientos →
        </a>
    </div>
    <!-- Dropdown End -->
</div>
