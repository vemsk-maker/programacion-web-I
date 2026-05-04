@extends('layouts.app')

@section('content')
<x-common.page-breadcrumb pageTitle="Lista de Productos" />

<div class="rounded-[2.5rem] border border-gray-100 bg-white p-8 shadow-sm">
    {{-- Header de la Tabla: Buscador y Botón Nuevo --}}
    <div class="mb-8 flex flex-wrap items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-[#1e293b]">Lista de Productos</h2>
            <p class="text-sm text-gray-500">Gestión de stock e inventario</p>
        </div>

        <div class="flex items-center gap-3">
            <form action="{{ route('products.index') }}" method="GET" class="flex gap-2">
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Buscar producto..."
                    class="h-11 w-64 rounded-xl border border-gray-200 px-4 text-sm focus:border-gray-400 focus:outline-none transition-all" />
                <button type="submit" class="h-11 rounded-xl bg-[#1e293b] px-6 text-sm font-bold text-white hover:bg-[#334155] transition-all">
                    Buscar
                </button>
            </form>

            <a href="{{ route('products.create') }}" 
                class="flex h-11 items-center gap-2 rounded-xl bg-[#e11d48] px-6 text-sm font-bold text-white shadow-md transition-all hover:bg-[#be123c] active:scale-95">
                <span class="text-lg">+</span> Nuevo Producto
            </a>
        </div>
    </div>

    {{-- Tabla de Productos --}}
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="border-b border-gray-50 text-[10px] font-bold uppercase tracking-widest text-gray-400">
                    <th class="pb-4 pl-4 text-left">Nombre</th>
                    <th class="pb-4 text-left">Categoría</th>
                    <th class="pb-4 text-left">Unidad</th>
                    <th class="pb-4 text-center">PEPS</th>
                    <th class="pb-4 text-center">Estado</th>
                    <th class="pb-4 pr-4 text-right">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($products as $product)
                <tr class="group transition-colors hover:bg-gray-50/50">
                    {{-- Nombre --}}
                    <td class="py-5 pl-4">
                        <div class="flex items-center gap-3">
                            <span class="h-2 w-2 rounded-full bg-orange-500"></span>
                            <span class="text-sm font-bold text-[#1e293b]">{{ $product->name }}</span>
                        </div>
                    </td>

                    {{-- Categoría --}}
                    <td class="py-5">
                        <span class="rounded-full bg-gray-100 px-3 py-1 text-[10px] font-bold text-gray-500 uppercase">
                            {{ $product->category->name ?? 'Sin categoría' }}
                        </span>
                    </td>

                    {{-- Unidad --}}
                    <td class="py-5 text-sm text-gray-500">
                        {{ $product->unit_of_measure }}
                    </td>

                    {{-- PEPS --}}
                    <td class="py-5 text-center">
                        @if($product->use_batches)
                            <span class="rounded-md bg-blue-50 px-2 py-1 text-[9px] font-bold text-blue-600 border border-blue-100">PEPS</span>
                        @else
                            <span class="text-[10px] text-gray-300">No aplica</span>
                        @endif
                    </td>

                    {{-- Estado --}}
                    <td class="py-5 text-center">
                        @if($product->active)
                            <span class="text-xs font-medium text-emerald-600">Activo</span>
                        @else
                            <span class="text-xs font-medium text-gray-400">Inactivo</span>
                        @endif
                    </td>

                    {{-- Acciones --}}
                    <td class="py-5 pr-4 text-right">
                        <div class="flex items-center justify-end gap-4">
                            <a href="{{ route('products.show', $product) }}" 
                                class="text-[11px] font-bold text-blue-500 hover:text-blue-700 transition-colors uppercase tracking-tighter">
                                Ver
                            </a>
                            
                            <a href="{{ route('products.edit', $product) }}" 
                                class="text-[11px] font-bold text-orange-500 hover:text-orange-700 transition-colors uppercase tracking-tighter">
                                Editar
                            </a>

                            <form action="{{ route('products.destroy', $product) }}" method="POST" class="inline" 
                                  onsubmit="return confirm('¿Eliminar este producto?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-[11px] font-bold text-[#e11d48] hover:text-[#be123c] transition-colors uppercase tracking-tighter">
                                    Eliminar
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="py-12 text-center text-sm text-gray-400 italic">
                        No se encontraron productos en el inventario.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Paginación --}}
    <div class="mt-8">
        {{ $products->links() }}
    </div>
</div>
@endsection