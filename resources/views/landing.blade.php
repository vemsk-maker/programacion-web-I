<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>AYMA Micromercado — Sistema de Gestión</title>
    <meta name="description" content="Sistema integral de gestión para AYMA Micromercado: inventario, ventas, compras y reportes en tiempo real." />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap');
        * { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-white text-[#1e293b] antialiased">

    {{-- ── Navbar ───────────────────────────────────────────────────────────── --}}
    <header class="fixed inset-x-0 top-0 z-50 border-b border-gray-100 bg-white/90 backdrop-blur-sm">
        <div class="mx-auto flex max-w-6xl items-center justify-between px-6 py-3">
            <div class="flex items-center gap-3">
                <img src="{{ asset('images/logo-ayma.png') }}" alt="AYMA" class="h-10 w-auto" />
                <div class="hidden sm:block">
                    <p class="text-sm font-black text-[#1e293b] leading-tight">AYMA</p>
                    <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400">Micromercado</p>
                </div>
            </div>
            <a href="{{ route('login') }}"
               class="flex items-center gap-2 rounded-xl bg-[#e11d48] px-5 py-2.5 text-sm font-bold text-white shadow-md shadow-red-100 transition-all hover:bg-[#be123c] active:scale-95">
                Ingresar al sistema
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                </svg>
            </a>
        </div>
    </header>

    {{-- ── Hero ────────────────────────────────────────────────────────────── --}}
    <section class="relative overflow-hidden pt-36 pb-16 sm:pt-44 sm:pb-24">
        {{-- Fondo decorativo --}}
        <div class="pointer-events-none absolute inset-0 -z-10">
            <div class="absolute -top-40 -right-40 h-[600px] w-[600px] rounded-full bg-red-50 opacity-60 blur-3xl"></div>
            <div class="absolute -bottom-20 -left-20 h-[400px] w-[400px] rounded-full bg-orange-50 opacity-50 blur-3xl"></div>
        </div>

        <div class="mx-auto max-w-6xl px-6">
            <div class="grid items-center gap-12 lg:grid-cols-2">

                {{-- Texto --}}
                <div class="order-2 lg:order-1">
                    <span class="inline-block rounded-full bg-red-50 px-4 py-1.5 text-xs font-black uppercase tracking-widest text-[#e11d48]">
                        Sistema de gestión
                    </span>
                    <h1 class="mt-4 text-4xl font-black leading-tight text-[#1e293b] sm:text-5xl lg:text-6xl">
                        Todo tu negocio,<br />
                        <span class="text-[#e11d48]">en un solo lugar</span>
                    </h1>
                    <p class="mt-5 text-lg font-medium leading-relaxed text-gray-500 max-w-lg">
                        Gestiona inventario, ventas, compras y reportes de AYMA Micromercado con precisión y en tiempo real desde cualquier dispositivo.
                    </p>

                    <div class="mt-8 flex flex-wrap items-center gap-4">
                        <a href="{{ route('login') }}"
                           class="flex items-center gap-2 rounded-2xl bg-[#e11d48] px-7 py-3.5 text-base font-black text-white shadow-lg shadow-red-200 transition-all hover:bg-[#be123c] hover:shadow-red-300 active:scale-95">
                            Acceder al sistema
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                            </svg>
                        </a>
                    </div>

                    {{-- Stats rápidas --}}
                    <div class="mt-10 flex flex-wrap gap-8">
                        <div>
                            <p class="text-3xl font-black text-[#1e293b]">100%</p>
                            <p class="text-sm font-medium text-gray-400">Control de stock</p>
                        </div>
                        <div class="w-px bg-gray-100"></div>
                        <div>
                            <p class="text-3xl font-black text-[#1e293b]">Real-time</p>
                            <p class="text-sm font-medium text-gray-400">Reportes en vivo</p>
                        </div>
                        <div class="w-px bg-gray-100"></div>
                        <div>
                            <p class="text-3xl font-black text-[#1e293b]">Multi-rol</p>
                            <p class="text-sm font-medium text-gray-400">Control de accesos</p>
                        </div>
                    </div>
                </div>

                {{-- Logo / Imagen hero --}}
                <div class="order-1 flex justify-center lg:order-2 lg:justify-end">
                    <div class="relative">
                        <img src="{{ asset('images/logo-ayma.png') }}"
                             alt="AYMA Micromercado"
                             class="h-56 w-auto object-contain drop-shadow-lg sm:h-72" />
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ── Módulos del sistema ────────────────────────────────────────────── --}}
    <section class="bg-gray-50/60 py-20">
        <div class="mx-auto max-w-6xl px-6">
            <div class="mb-12 text-center">
                <h2 class="text-3xl font-black text-[#1e293b] sm:text-4xl">¿Qué incluye el sistema?</h2>
                <p class="mt-3 text-base font-medium text-gray-400">Todas las herramientas que tu negocio necesita, integradas.</p>
            </div>

            <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">

                {{-- Inventario --}}
                <div class="group rounded-3xl border border-gray-100 bg-white p-7 shadow-sm transition-all hover:border-red-100 hover:shadow-md hover:shadow-red-50">
                    <div class="mb-5 flex h-12 w-12 items-center justify-center rounded-2xl bg-red-50 text-[#e11d48] transition-colors group-hover:bg-[#e11d48] group-hover:text-white">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10"/>
                        </svg>
                    </div>
                    <h3 class="text-base font-black text-[#1e293b]">Inventario</h3>
                    <p class="mt-2 text-sm font-medium leading-relaxed text-gray-400">Controla compras, traslados y ajustes de stock entre sucursales y almacenes.</p>
                    <ul class="mt-4 space-y-1.5 text-xs font-semibold text-gray-500">
                        <li class="flex items-center gap-2"><span class="h-1.5 w-1.5 rounded-full bg-[#e11d48]"></span>Registro de compras</li>
                        <li class="flex items-center gap-2"><span class="h-1.5 w-1.5 rounded-full bg-[#e11d48]"></span>Traslados entre ubicaciones</li>
                        <li class="flex items-center gap-2"><span class="h-1.5 w-1.5 rounded-full bg-[#e11d48]"></span>Ajustes de stock manual</li>
                    </ul>
                </div>

                {{-- Ventas / POS --}}
                <div class="group rounded-3xl border border-gray-100 bg-white p-7 shadow-sm transition-all hover:border-blue-100 hover:shadow-md hover:shadow-blue-50">
                    <div class="mb-5 flex h-12 w-12 items-center justify-center rounded-2xl bg-blue-50 text-blue-600 transition-colors group-hover:bg-blue-600 group-hover:text-white">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-base font-black text-[#1e293b]">Punto de Venta</h3>
                    <p class="mt-2 text-sm font-medium leading-relaxed text-gray-400">Sistema POS ágil para registrar ventas, buscar productos por código de barras y emitir recibos.</p>
                    <ul class="mt-4 space-y-1.5 text-xs font-semibold text-gray-500">
                        <li class="flex items-center gap-2"><span class="h-1.5 w-1.5 rounded-full bg-blue-500"></span>Búsqueda por código de barras</li>
                        <li class="flex items-center gap-2"><span class="h-1.5 w-1.5 rounded-full bg-blue-500"></span>Historial de ventas</li>
                        <li class="flex items-center gap-2"><span class="h-1.5 w-1.5 rounded-full bg-blue-500"></span>Cancelación de ventas</li>
                    </ul>
                </div>

                {{-- Productos --}}
                <div class="group rounded-3xl border border-gray-100 bg-white p-7 shadow-sm transition-all hover:border-green-100 hover:shadow-md hover:shadow-green-50">
                    <div class="mb-5 flex h-12 w-12 items-center justify-center rounded-2xl bg-green-50 text-green-600 transition-colors group-hover:bg-green-600 group-hover:text-white">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                        </svg>
                    </div>
                    <h3 class="text-base font-black text-[#1e293b]">Catálogo</h3>
                    <p class="mt-2 text-sm font-medium leading-relaxed text-gray-400">Administra productos, categorías y proveedores con control de vencimientos y lotes.</p>
                    <ul class="mt-4 space-y-1.5 text-xs font-semibold text-gray-500">
                        <li class="flex items-center gap-2"><span class="h-1.5 w-1.5 rounded-full bg-green-500"></span>Gestión de lotes y vencimientos</li>
                        <li class="flex items-center gap-2"><span class="h-1.5 w-1.5 rounded-full bg-green-500"></span>Múltiples códigos de barras</li>
                        <li class="flex items-center gap-2"><span class="h-1.5 w-1.5 rounded-full bg-green-500"></span>Categorías y proveedores</li>
                    </ul>
                </div>

                {{-- Reportes --}}
                <div class="group rounded-3xl border border-gray-100 bg-white p-7 shadow-sm transition-all hover:border-purple-100 hover:shadow-md hover:shadow-purple-50">
                    <div class="mb-5 flex h-12 w-12 items-center justify-center rounded-2xl bg-purple-50 text-purple-600 transition-colors group-hover:bg-purple-600 group-hover:text-white">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                    </div>
                    <h3 class="text-base font-black text-[#1e293b]">Reportes</h3>
                    <p class="mt-2 text-sm font-medium leading-relaxed text-gray-400">Visualiza el estado del negocio con reportes de stock, movimientos y vencimientos.</p>
                    <ul class="mt-4 space-y-1.5 text-xs font-semibold text-gray-500">
                        <li class="flex items-center gap-2"><span class="h-1.5 w-1.5 rounded-full bg-purple-500"></span>Stock en tiempo real</li>
                        <li class="flex items-center gap-2"><span class="h-1.5 w-1.5 rounded-full bg-purple-500"></span>Kardex de movimientos</li>
                        <li class="flex items-center gap-2"><span class="h-1.5 w-1.5 rounded-full bg-purple-500"></span>Alertas de vencimiento</li>
                    </ul>
                </div>

                {{-- Usuarios --}}
                <div class="group rounded-3xl border border-gray-100 bg-white p-7 shadow-sm transition-all hover:border-orange-100 hover:shadow-md hover:shadow-orange-50">
                    <div class="mb-5 flex h-12 w-12 items-center justify-center rounded-2xl bg-orange-50 text-orange-600 transition-colors group-hover:bg-orange-600 group-hover:text-white">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-base font-black text-[#1e293b]">Usuarios y Roles</h3>
                    <p class="mt-2 text-sm font-medium leading-relaxed text-gray-400">Control de accesos con roles diferenciados según la responsabilidad de cada empleado.</p>
                    <ul class="mt-4 space-y-1.5 text-xs font-semibold text-gray-500">
                        <li class="flex items-center gap-2"><span class="h-1.5 w-1.5 rounded-full bg-orange-500"></span>Master, Admin, Almacenero</li>
                        <li class="flex items-center gap-2"><span class="h-1.5 w-1.5 rounded-full bg-orange-500"></span>Cajero, Visor</li>
                        <li class="flex items-center gap-2"><span class="h-1.5 w-1.5 rounded-full bg-orange-500"></span>Asignación por sucursal</li>
                    </ul>
                </div>

                {{-- Sucursales --}}
                <div class="group rounded-3xl border border-gray-100 bg-white p-7 shadow-sm transition-all hover:border-amber-100 hover:shadow-md hover:shadow-amber-50">
                    <div class="mb-5 flex h-12 w-12 items-center justify-center rounded-2xl bg-amber-50 text-amber-600 transition-colors group-hover:bg-amber-600 group-hover:text-white">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-2 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                    </div>
                    <h3 class="text-base font-black text-[#1e293b]">Sucursales</h3>
                    <p class="mt-2 text-sm font-medium leading-relaxed text-gray-400">Administra múltiples sucursales y sus almacenes asociados desde un único panel.</p>
                    <ul class="mt-4 space-y-1.5 text-xs font-semibold text-gray-500">
                        <li class="flex items-center gap-2"><span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>Sucursales y almacenes</li>
                        <li class="flex items-center gap-2"><span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>Stock por ubicación</li>
                        <li class="flex items-center gap-2"><span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>Ubicación de merma</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    {{-- ── CTA Final ───────────────────────────────────────────────────────── --}}
    <section class="py-20">
        <div class="mx-auto max-w-3xl px-6 text-center">
            <img src="{{ asset('images/logo-ayma.png') }}" alt="AYMA" class="mx-auto mb-6 h-20 w-auto drop-shadow-md" />
            <h2 class="text-3xl font-black text-[#1e293b] sm:text-4xl">Listo para empezar</h2>
            <p class="mt-4 text-base font-medium text-gray-400">
                Ingresa con tus credenciales y toma el control de tu negocio hoy mismo.
            </p>
            <a href="{{ route('login') }}"
               class="mt-8 inline-flex items-center gap-3 rounded-2xl bg-[#e11d48] px-10 py-4 text-base font-black text-white shadow-xl shadow-red-200 transition-all hover:bg-[#be123c] hover:shadow-red-300 active:scale-95">
                Acceder al sistema
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                </svg>
            </a>
        </div>
    </section>

    {{-- ── Footer ──────────────────────────────────────────────────────────── --}}
    <footer class="border-t border-gray-100 py-8">
        <div class="mx-auto max-w-6xl px-6 flex flex-wrap items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <img src="{{ asset('images/logo-ayma.png') }}" alt="AYMA" class="h-8 w-auto" />
                <span class="text-sm font-black text-gray-400">AYMA Micromercado</span>
            </div>
            <p class="text-xs font-medium text-gray-300">Sistema de gestión interno · {{ date('Y') }}</p>
        </div>
    </footer>

</body>
</html>
