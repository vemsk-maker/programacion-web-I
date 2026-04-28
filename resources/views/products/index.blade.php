@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Productos" />

    {{-- Flash messages --}}
    @if(session('success'))
        <div class="mb-4 rounded-lg bg-success-50 border border-success-200 px-4 py-3 text-sm text-success-700 dark:bg-success-500/10 dark:border-success-500/20 dark:text-success-400">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="mb-4 rounded-lg bg-error-50 border border-error-200 px-4 py-3 text-sm text-error-700 dark:bg-error-500/10 dark:border-error-500/20 dark:text-error-400">
            {{ session('error') }}
        </div>
    @endif

    <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
        {{-- Header --}}
        <div class="flex flex-wrap items-center justify-between gap-3 px-6 py-4 border-b border-gray-100 dark:border-gray-800">
            <h3 class="text-base font-medium text-gray-800 dark:text-white/90">Lista de Productos</h3>
            <div class="flex flex-wrap items-center gap-3">
                <form method="GET" action="{{ route('products.index') }}" class="flex flex-wrap items-center gap-2">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar producto..."
                        class="shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-10 rounded-lg border border-gray-300 bg-transparent px-3 py-2 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30" />

                    {{-- Filter by category --}}
                    <div x-data="{ isOptionSelected: {{ request('category_id') ? 'true' : 'false' }} }" class="relative z-20">
                        <select name="category_id"
                            class="shadow-theme-xs h-10 appearance-none rounded-lg border border-gray-300 bg-transparent px-3 pr-9 text-sm focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"
                            :class="isOptionSelected ? 'text-gray-800 dark:text-white/90' : 'text-gray-400 dark:text-gray-500'"
                            @change="isOptionSelected = true">
                            <option value="">Todas las categorías</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}
                                    class="dark:bg-gray-900 dark:text-gray-400">
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                        <span class="pointer-events-none absolute top-1/2 right-3 -translate-y-1/2 text-gray-500 dark:text-gray-400">
                            <svg class="stroke-current" width="16" height="16" viewBox="0 0 20 20" fill="none"><path d="M4.79175 7.396L10.0001 12.6043L15.2084 7.396" stroke="" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </span>
                    </div>

                    <button type="submit" class="flex h-10 items-center gap-1.5 rounded-lg bg-gray-100 px-3 text-sm font-medium text-gray-700 hover:bg-gray-200 dark:bg-white/10 dark:text-white/80 dark:hover:bg-white/20">
                        Filtrar
                    </button>
                </form>
                <a href="{{ route('products.create') }}"
                   class="flex items-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600">
                    + Nuevo Producto
                </a>
            </div>
        </div>

        {{-- Table --}}
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 dark:border-gray-800">
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Nombre</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Categoría</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Unidad</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Lotes</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Estado</th>
                        <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($products as $product)
                        <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.02]">
                            <td class="px-6 py-3 font-medium text-gray-800 dark:text-white/90">
                                <a href="{{ route('products.show', $product) }}" class="hover:text-brand-500">
                                    {{ $product->name }}
                                </a>
                            </td>
                            <td class="px-6 py-3 text-gray-500 dark:text-gray-400">
                                {{ $product->category->name }}
                            </td>
                            <td class="px-6 py-3 text-gray-500 dark:text-gray-400">
                                {{ $product->unit_of_measure }}
                            </td>
                            <td class="px-6 py-3">
                                @if($product->use_batches)
                                    <span class="inline-flex items-center rounded-full bg-brand-50 px-2.5 py-0.5 text-xs font-medium text-brand-600 dark:bg-brand-500/10 dark:text-brand-400">
                                        PEPS
                                    </span>
                                @else
                                    <span class="text-xs text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-6 py-3">
                                @if($product->active)
                                    <span class="inline-flex items-center rounded-full bg-success-50 px-2.5 py-0.5 text-xs font-medium text-success-700 dark:bg-success-500/10 dark:text-success-400">Activo</span>
                                @else
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-500 dark:bg-white/10 dark:text-gray-400">Inactivo</span>
                                @endif
                            </td>
                            <td class="px-6 py-3 text-right">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('products.show', $product) }}"
                                       class="rounded-lg bg-gray-100 px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-200 dark:bg-white/10 dark:text-white/80 dark:hover:bg-white/20">
                                        Ver
                                    </a>
                                    <a href="{{ route('products.edit', $product) }}"
                                       class="rounded-lg bg-gray-100 px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-200 dark:bg-white/10 dark:text-white/80 dark:hover:bg-white/20">
                                        Editar
                                    </a>
                                    <form method="POST" action="{{ route('products.destroy', $product) }}"
                                          onsubmit="return confirm('¿Eliminar este producto?')">
                                        @csrf @method('DELETE')
                                        <button type="submit"
                                                class="rounded-lg bg-error-50 px-3 py-1.5 text-xs font-medium text-error-600 hover:bg-error-100 dark:bg-error-500/10 dark:text-error-400 dark:hover:bg-error-500/20">
                                            Eliminar
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-10 text-center text-sm text-gray-400 dark:text-gray-500">
                                No hay productos registrados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($products->hasPages())
            <div class="border-t border-gray-100 px-6 py-4 dark:border-gray-800">
                {{ $products->links() }}
            </div>
        @endif
    </div>
@endsection
