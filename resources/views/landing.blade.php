<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    {{-- SEO --}}
    <title>AYMA Micromercado | Supermercado y Licorería en Sucre, Bolivia</title>
    <meta name="description" content="AYMA Micromercado en Sucre, Bolivia. Supermercado con abarrotes, lácteos, carnes, bebidas, licorería y mucho más. Dos locales para servirte. ¡Visítanos!" />
    <meta name="keywords" content="micromercado sucre, supermercado sucre bolivia, minimercado sucre, tienda de abarrotes sucre, licorería sucre, AYMA micromercado, mercado sucre, compras sucre" />
    <meta name="robots" content="index, follow" />
    <link rel="canonical" href="{{ url('/') }}" />

    {{-- Open Graph --}}
    <meta property="og:type" content="business.business" />
    <meta property="og:title" content="AYMA Micromercado — Supermercado y Licorería en Sucre" />
    <meta property="og:description" content="Tu minimercado de confianza en Sucre. Abarrotes, carnes, lácteos, bebidas, licorería y más. Visítanos." />
    <meta property="og:image" content="{{ asset('images/logo-ayma.png') }}" />
    <meta property="og:url" content="{{ url('/') }}" />
    <meta property="og:locale" content="es_BO" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet" />

    <style>
        * { font-family: 'Inter', sans-serif; }
        html { scroll-behavior: smooth; }
        .hero-gradient {
            background: radial-gradient(ellipse 80% 60% at 60% 0%, #fff1f2 0%, transparent 70%),
                        radial-gradient(ellipse 50% 40% at 0% 80%, #fff7ed 0%, transparent 60%),
                        #ffffff;
        }
        .dept-card:hover .dept-icon { transform: scale(1.15); }
        .dept-icon { transition: transform 0.2s ease; }

        /* Sección Locales — tarjetas sobre fondo oscuro */
        .locale-card {
            background-color: rgba(255,255,255,0.12);
            border-radius: 1.5rem;
            border: 1px solid rgba(255,255,255,0.2);
            padding: 2rem;
            transition: background-color 0.2s, border-color 0.2s;
        }
        .locale-card:hover {
            background-color: rgba(255,255,255,0.18);
            border-color: rgba(255,255,255,0.3);
        }
        .locale-text        { color: rgba(255,255,255,0.92); }
        .locale-text-sub    { color: rgba(255,255,255,0.7); }
        .locale-text-muted  { color: rgba(255,255,255,0.55); }
    </style>

    {{-- Schema.org LocalBusiness --}}
    @php
    $schemaOrg = json_encode([
        '@context'      => 'https://schema.org',
        '@type'         => 'GroceryStore',
        'name'          => 'AYMA Micromercado',
        'description'   => 'Supermercado y licorería en Sucre, Bolivia. Abarrotes, lácteos, carnes, bebidas y más.',
        'url'           => url('/'),
        'logo'          => asset('images/logo-ayma.png'),
        'image'         => asset('images/logo-ayma.png'),
        'address'       => [
            '@type'           => 'PostalAddress',
            'addressLocality' => 'Sucre',
            'addressRegion'   => 'Chuquisaca',
            'addressCountry'  => 'BO',
        ],
        'openingHours' => 'Mo-Su 07:00-21:00',
        'priceRange'   => 'Bs',
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    @endphp
    <script type="application/ld+json">{!! $schemaOrg !!}</script>
</head>
<body class="bg-white text-[#1e293b] antialiased">

    {{-- NAVBAR --}}
    <header class="fixed inset-x-0 top-0 z-50 border-b border-gray-100 bg-white/95 backdrop-blur-sm">
        <div class="mx-auto flex max-w-6xl items-center justify-between px-6 py-3">
            <a href="#" class="flex items-center gap-3">
                <img src="{{ asset('images/logo-ayma.png') }}" alt="AYMA Micromercado" class="h-10 w-auto" />
                <div>
                    <p class="text-sm font-black text-[#1e293b] leading-tight">AYMA Micromercado</p>
                    <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400">Sucre, Bolivia</p>
                </div>
            </a>
            <nav class="hidden sm:flex items-center gap-8 text-sm font-semibold text-gray-500">
                <a href="#departamentos" class="hover:text-[#e11d48] transition-colors">Productos</a>
                <a href="#locales" class="hover:text-[#e11d48] transition-colors">Locales</a>
                <a href="#contacto" class="hover:text-[#e11d48] transition-colors">Contacto</a>
            </nav>
        </div>
    </header>

    <main>

    {{-- HERO --}}
    <section class="hero-gradient pt-32 pb-20 sm:pt-40 sm:pb-28" aria-label="Presentación">
        <div class="mx-auto max-w-6xl px-6">
            <div class="grid items-center gap-12 lg:grid-cols-2">

                <div class="order-2 lg:order-1">
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-red-50 px-4 py-1.5 text-xs font-black uppercase tracking-widest text-[#e11d48]">
                        <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/></svg>
                        Sucre, Bolivia
                    </span>

                    <h1 class="mt-4 text-4xl font-black leading-tight text-[#1e293b] sm:text-5xl xl:text-6xl">
                        Tu minimercado<br />
                        <span class="text-[#e11d48]">de confianza</span>
                    </h1>

                    <p class="mt-5 max-w-lg text-lg font-medium leading-relaxed text-gray-500">
                        En <strong class="font-bold text-[#1e293b]">AYMA</strong> encontrás todo lo que necesitás: abarrotes, carnes frescas, lácteos, bebidas, licorería y mucho más. Dos locales en Sucre para servirte mejor.
                    </p>

                    <div class="mt-8 flex flex-wrap items-center gap-4">
                        <a href="#locales"
                           class="flex items-center gap-2 rounded-2xl bg-[#e11d48] px-7 py-3.5 text-base font-black text-white shadow-lg shadow-red-100 transition-all hover:bg-[#be123c] active:scale-95">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            ¿Dónde estamos?
                        </a>
                        <a href="#departamentos"
                           class="flex items-center gap-2 rounded-2xl border-2 border-gray-200 bg-white px-7 py-3.5 text-base font-bold text-[#1e293b] transition-all hover:border-gray-300 hover:bg-gray-50 active:scale-95">
                            Ver productos
                        </a>
                    </div>

                    <div class="mt-10 flex flex-wrap gap-4">
                        <div class="flex items-center gap-2 rounded-xl bg-gray-50 px-4 py-2.5">
                            <span class="text-xl">🛒</span>
                            <div>
                                <p class="text-xs font-black text-[#1e293b]">Supermercado</p>
                                <p class="text-[10px] font-medium text-gray-400">Todos los productos</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 rounded-xl bg-gray-50 px-4 py-2.5">
                            <span class="text-xl">🍺</span>
                            <div>
                                <p class="text-xs font-black text-[#1e293b]">Licorería</p>
                                <p class="text-[10px] font-medium text-gray-400">Bebidas y licores</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 rounded-xl bg-gray-50 px-4 py-2.5">
                            <span class="text-xl">📍</span>
                            <div>
                                <p class="text-xs font-black text-[#1e293b]">2 locales</p>
                                <p class="text-[10px] font-medium text-gray-400">En Sucre</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="order-1 flex items-center justify-center lg:order-2">
                    <div class="relative flex h-72 w-72 items-center justify-center rounded-full bg-red-50 sm:h-80 sm:w-80">
                        <div class="absolute inset-0 rounded-full border-2 border-red-100 opacity-60 scale-110"></div>
                        <div class="absolute inset-0 rounded-full border border-red-50 opacity-40 scale-125"></div>
                        <img src="{{ asset('images/logo-ayma.png') }}"
                             alt="AYMA Micromercado — Supermercado y Licorería en Sucre Bolivia"
                             class="relative z-10 h-52 w-auto drop-shadow-xl" />
                    </div>
                </div>

            </div>
        </div>
    </section>

    {{-- DEPARTAMENTOS --}}
    <section id="departamentos" class="bg-gray-50/60 py-20 sm:py-24" aria-label="Departamentos y productos">
        <div class="mx-auto max-w-6xl px-6">

            <div class="mb-12 text-center">
                <span class="inline-block rounded-full bg-red-50 px-4 py-1.5 text-xs font-black uppercase tracking-widest text-[#e11d48]">Lo que encontrás</span>
                <h2 class="mt-3 text-3xl font-black text-[#1e293b] sm:text-4xl">Todo en un mismo lugar</h2>
                <p class="mt-3 text-base font-medium text-gray-400">Más de 28 departamentos con los mejores productos para tu hogar</p>
            </div>

            <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
                @php
                $depts = [
                    ['icon'=>'🥛','name'=>'Lácteos y Huevos',    'desc'=>'Leche, yogurt, quesos, huevos'],
                    ['icon'=>'🥩','name'=>'Carnes y Embutidos',  'desc'=>'Carnes frescas, embutidos'],
                    ['icon'=>'🥫','name'=>'Abarrotes',            'desc'=>'Aceites, harinas, conservas'],
                    ['icon'=>'🍚','name'=>'Arroz y Fideos',       'desc'=>'Cereales y pastas'],
                    ['icon'=>'🧃','name'=>'Aguas y Jugos',        'desc'=>'Jugos naturales y néctares'],
                    ['icon'=>'🥤','name'=>'Gaseosas',             'desc'=>'Refrescos y bebidas'],
                    ['icon'=>'🍺','name'=>'Cervezas y Licores',   'desc'=>'Amplio surtido de bebidas'],
                    ['icon'=>'⚡','name'=>'Energizantes',         'desc'=>'Red Bull, Monster y más'],
                    ['icon'=>'🍪','name'=>'Galletas y Snacks',    'desc'=>'Galletas, papas fritas'],
                    ['icon'=>'🍫','name'=>'Chocolates y Dulces',  'desc'=>'Confitería variada'],
                    ['icon'=>'🧴','name'=>'Higiene Personal',     'desc'=>'Jabones, shampoo y más'],
                    ['icon'=>'🧹','name'=>'Limpieza del Hogar',   'desc'=>'Detergentes, desinfectantes'],
                ];
                @endphp

                @foreach($depts as $d)
                <article class="dept-card group rounded-2xl border border-gray-100 bg-white p-5 shadow-sm transition-all hover:border-red-100 hover:shadow-md">
                    <div class="dept-icon mb-3 inline-block text-3xl">{{ $d['icon'] }}</div>
                    <h3 class="text-sm font-black text-[#1e293b]">{{ $d['name'] }}</h3>
                    <p class="mt-0.5 text-xs font-medium text-gray-400">{{ $d['desc'] }}</p>
                </article>
                @endforeach
            </div>
        </div>
    </section>

    {{-- POR QUÉ ELEGIRNOS --}}
    <section class="py-20 sm:py-24" aria-label="Por qué elegirnos">
        <div class="mx-auto max-w-6xl px-6">
            <div class="grid gap-8 sm:grid-cols-3">
                <div class="text-center">
                    <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-2xl bg-red-50 text-3xl">🏪</div>
                    <h3 class="text-base font-black text-[#1e293b]">Dos locales</h3>
                    <p class="mt-2 text-sm font-medium text-gray-400">Supermercado y Licorería, siempre cerca de vos.</p>
                </div>
                <div class="text-center">
                    <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-2xl bg-orange-50 text-3xl">✅</div>
                    <h3 class="text-base font-black text-[#1e293b]">Productos frescos</h3>
                    <p class="mt-2 text-sm font-medium text-gray-400">Control de calidad y fechas de vencimiento en todo nuestro stock.</p>
                </div>
                <div class="text-center">
                    <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-2xl bg-emerald-50 text-3xl">💸</div>
                    <h3 class="text-base font-black text-[#1e293b]">Precios accesibles</h3>
                    <p class="mt-2 text-sm font-medium text-gray-400">Los mejores precios de la zona para tu economía familiar.</p>
                </div>
            </div>
        </div>
    </section>

    {{-- LOCALES --}}
    <section id="locales" class="bg-[#1e293b] py-20 sm:py-24" aria-label="Nuestros locales en Sucre">
        <div class="mx-auto max-w-6xl px-6">

            <div class="mb-12 text-center">
                <span class="inline-block rounded-full bg-white/10 px-4 py-1.5 text-xs font-black uppercase tracking-widest text-white/60">Dónde estamos</span>
                <h2 class="mt-3 text-3xl font-black text-white sm:text-4xl">Nuestros locales</h2>
                <p class="mt-3 text-base font-medium text-white/50">Dos locales en Sucre para atenderte</p>
            </div>

            <div class="grid gap-6 sm:grid-cols-2">

                <article class="locale-card">
                    <div class="mb-5 flex items-center gap-4">
                        <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-[#e11d48] text-2xl">🛒</div>
                        <div>
                            <h3 class="text-xl font-black text-white">AYMA Supermercado</h3>
                            <p class="text-sm font-medium locale-text-sub">Abarrotes, frescos y más</p>
                        </div>
                    </div>
                    <ul class="space-y-3">
                        <li class="flex items-start gap-3 text-sm font-medium locale-text">
                            <svg class="mt-0.5 h-4 w-4 shrink-0 text-[#e11d48]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                            <span>Sucre, Bolivia &mdash; <em class="not-italic locale-text-muted">dirección exacta por confirmar</em></span>
                        </li>
                        <li class="flex items-center gap-3 text-sm font-medium locale-text">
                            <svg class="h-4 w-4 shrink-0 text-[#e11d48]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Lun&ndash;Sáb: 7:00 &ndash; 21:00 &nbsp;&middot;&nbsp; Dom: 8:00 &ndash; 20:00
                        </li>
                        <li class="flex items-center gap-3 text-sm font-medium locale-text-muted">
                            <svg class="h-4 w-4 shrink-0 text-[#e11d48]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                            <em class="not-italic">Número por confirmar</em>
                        </li>
                    </ul>
                </article>

                <article class="locale-card">
                    <div class="mb-5 flex items-center gap-4">
                        <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-amber-500 text-2xl">🍺</div>
                        <div>
                            <h3 class="text-xl font-black text-white">AYMA Licorería</h3>
                            <p class="text-sm font-medium locale-text-sub">Cervezas, vinos y licores</p>
                        </div>
                    </div>
                    <ul class="space-y-3">
                        <li class="flex items-start gap-3 text-sm font-medium locale-text">
                            <svg class="mt-0.5 h-4 w-4 shrink-0 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                            <span>Sucre, Bolivia &mdash; <em class="not-italic locale-text-muted">dirección exacta por confirmar</em></span>
                        </li>
                        <li class="flex items-center gap-3 text-sm font-medium locale-text">
                            <svg class="h-4 w-4 shrink-0 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Lun&ndash;Sáb: 10:00 &ndash; 22:00 &nbsp;&middot;&nbsp; Dom: 11:00 &ndash; 21:00
                        </li>
                        <li class="flex items-center gap-3 text-sm font-medium locale-text-muted">
                            <svg class="h-4 w-4 shrink-0 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                            <em class="not-italic">Número por confirmar</em>
                        </li>
                    </ul>
                </article>

            </div>
        </div>
    </section>

    {{-- CONTACTO --}}
    <section id="contacto" class="py-20 sm:py-24" aria-label="Contacto">
        <div class="mx-auto max-w-3xl px-6 text-center">
            <span class="inline-block rounded-full bg-red-50 px-4 py-1.5 text-xs font-black uppercase tracking-widest text-[#e11d48]">Contacto</span>
            <h2 class="mt-3 text-3xl font-black text-[#1e293b] sm:text-4xl">¿Tenés alguna consulta?</h2>
            <p class="mt-4 text-base font-medium text-gray-400">
                Escribinos por WhatsApp o visítanos directamente en cualquiera de nuestros locales.
            </p>
            <div class="mt-8 flex flex-wrap items-center justify-center gap-4">
                <a href="https://wa.me/591XXXXXXXXX?text=Hola%20AYMA%20Micromercado%2C%20quisiera%20consultar..."
                   target="_blank" rel="noopener noreferrer"
                   class="flex items-center gap-2.5 rounded-2xl bg-[#25D366] px-7 py-3.5 text-base font-black text-white shadow-lg shadow-green-100 transition-all hover:bg-[#1ebe5d] active:scale-95">
                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51a12.8 12.8 0 00-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                    Escribir por WhatsApp
                </a>
                <a href="#locales"
                   class="flex items-center gap-2 rounded-2xl border-2 border-gray-200 bg-white px-7 py-3.5 text-base font-bold text-[#1e293b] transition-all hover:border-gray-300 hover:bg-gray-50 active:scale-95">
                    Ver locales
                </a>
            </div>
        </div>
    </section>

    </main>

    {{-- FOOTER + acceso al sistema al fondo --}}
    <footer class="border-t border-gray-100 bg-gray-50 py-10">
        <div class="mx-auto max-w-6xl px-6">

            <div class="flex flex-col items-center gap-6 sm:flex-row sm:justify-between">
                <div class="flex items-center gap-3">
                    <img src="{{ asset('images/logo-ayma.png') }}" alt="AYMA" class="h-8 w-auto opacity-60" />
                    <div>
                        <p class="text-sm font-black text-gray-400">AYMA Micromercado</p>
                        <p class="text-xs font-medium text-gray-300">Sucre, Bolivia &middot; {{ date('Y') }}</p>
                    </div>
                </div>
                <div class="flex flex-wrap items-center justify-center gap-4 text-xs text-gray-400">
                    <a href="#departamentos" class="hover:text-[#e11d48] transition-colors">Productos</a>
                    <span class="text-gray-200">&middot;</span>
                    <a href="#locales" class="hover:text-[#e11d48] transition-colors">Locales</a>
                    <span class="text-gray-200">&middot;</span>
                    <a href="#contacto" class="hover:text-[#e11d48] transition-colors">Contacto</a>
                </div>
            </div>

            <div class="my-8 border-t border-gray-100"></div>

            {{-- Acceso al sistema, discreto, para el personal --}}
            <div class="flex items-center justify-center gap-3">
                <span class="text-xs font-medium text-gray-300">¿Sos del equipo AYMA?</span>
                <a href="{{ route('login') }}"
                   class="inline-flex items-center gap-1.5 rounded-xl border border-gray-200 bg-white px-4 py-2 text-xs font-bold text-gray-400 transition-all hover:border-gray-300 hover:text-[#1e293b] hover:shadow-sm">
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
                    Acceso al sistema
                </a>
            </div>

        </div>
    </footer>

</body>
</html>
