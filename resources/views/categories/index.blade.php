@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Categorías" />

    {{-- Mensajes de Retroalimentación --}}
    @if(session('success'))
        <div class="mb-4 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700">
            {{ session('success') }}
        </div>
    @endif

    <div class="rounded-2xl border border-gray-200 bg-white shadow-sm overflow-hidden">
        {{-- Header con Identidad Ayma --}}
        <div class="flex flex-wrap items-center justify-between gap-3 px-6 py-5 border-b border-gray-100">
            <div>
                <h3 class="text-lg font-bold text-gray-800">Lista de Categorías</h3>
                <p class="text-xs text-gray-500">Gestión de agrupación de productos</p>
            </div>
            
            <div class="flex flex-wrap items-center gap-3">
                {{-- Buscador mejorado --}}
                <form method="GET" action="{{ route('categories.index') }}" class="flex items-center gap-2">
                    <div class="relative">
                        <input
                            type="text"
                            name="search"
                            value="{{ request('search') }}"
                            placeholder="Buscar categoría..."
                            class="h-10 rounded-lg border border-gray-300 bg-gray-50 px-4 py-2 text-sm text-gray-800 focus:border-orange-500 focus:ring-2 focus:ring-orange-200 focus:outline-none transition-all w-48 md:w-64"
                        />
                    </div>
                    <button type="submit" class="h-10 rounded-lg bg-gray-800 px-4 text-sm font-bold text-white hover:bg-gray-700 transition">
                        Buscar
                    </button>
                    @if(request('search'))
                        <a href="{{ route('categories.index') }}" class="text-gray-400 hover:text-red-600 transition" title="Limpiar búsqueda">
                            <i class="fas fa-times-circle text-xl"></i>
                        </a>
                    @endif
                </form>

                {{-- Botón con color Rojo Ayma --}}
                <a href="{{ route('categories.create') }}"
                   class="flex items-center gap-2 rounded-lg bg-red-600 px-5 py-2.5 text-sm font-bold text-white hover:bg-red-700 shadow-md transition-all active:transform active:scale-95">
                    <span class="text-lg leading-none">+</span> Nueva Categoría
                </a>
            </div>
        </div>

        {{-- Tabla Estilizada --}}
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100">
                        <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-gray-600">Nombre</th>
                        <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-gray-600">Categoría Padre</th>
                        <th class="px-6 py-4 text-right text-xs font-bold uppercase tracking-wider text-gray-600">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-gray-700">
                    @forelse($categories as $category)
                        <tr class="hover:bg-orange-50/50 transition-colors">
                            <td class="px-6 py-4 font-semibold text-gray-900">
                                <div class="flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-orange-400"></span>
                                    {{ $category->name }}
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                @if($category->parent)
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-50 text-blue-700 border border-blue-100">
                                        {{ $category->parent->name }}
                                    </span>
                                @else
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-500 border border-gray-200 italic">
                                        Raíz
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex justify-end gap-3">
                                    {{-- Botón Editar Naranja --}}
                                    <a href="{{ route('categories.edit', $category) }}"
                                       class="flex items-center gap-1 text-orange-600 hover:text-orange-800 font-bold transition">
                                        <i class="fas fa-edit"></i> Editar
                                    </a>
                                    
                                    {{-- Botón Eliminar Rojo --}}
                                    <form method="POST" action="{{ route('categories.destroy', $category) }}"
                                          onsubmit="return confirm('¿Estás seguro de eliminar la categoría &quot;{{ $category->name }}&quot;?')">
                                        @csrf @method('DELETE')
                                        <button type="submit"
                                                class="flex items-center gap-1 text-red-500 hover:text-red-700 font-bold transition">
                                            <i class="fas fa-trash"></i> Eliminar
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center">
                                    <div class="p-4 bg-gray-50 rounded-full mb-3">
                                        <i class="fas fa-folder-open text-3xl text-gray-300"></i>
                                    </div>
                                    <p class="text-gray-500 font-medium">No hay categorías registradas que coincidan con la búsqueda.</p>
                                    <a href="{{ route('categories.index') }}" class="mt-2 text-orange-500 hover:underline text-sm font-bold">Ver todas las categorías</a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Paginación Ayma --}}
        @if($categories->hasPages())
            <div class="border-t border-gray-100 px-6 py-4 bg-gray-50">
                {{ $categories->appends(['search' => request('search')])->links() }}
            </div>
        @endif
    </div>
@endsection