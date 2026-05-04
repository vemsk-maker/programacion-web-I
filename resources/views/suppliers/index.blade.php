@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Proveedores" />

    {{-- Contenedor Principal Blanco 3xl --}}
    <div class="rounded-3xl border border-gray-200 bg-white shadow-sm overflow-hidden">
        
        {{-- Header con Buscador y Botón --}}
        <div class="flex flex-wrap items-center justify-between gap-3 px-6 py-5 border-b border-gray-100 bg-white">
            <div>
                <h3 class="text-xl font-bold text-gray-900">Lista de Proveedores</h3>
                <p class="text-sm text-gray-900 font-medium">Gestión de alianzas y suministros</p>
            </div>

            <div class="flex items-center gap-2">
                <form method="GET" action="{{ route('suppliers.index') }}" class="flex items-center gap-2">
                    <input
                        type="text"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Buscar proveedor..."
                        class="h-11 w-64 rounded-xl border border-gray-200 bg-gray-50 px-4 py-2 text-sm text-gray-800 focus:border-orange-500 focus:ring-0 outline-none"
                    />
                    <button type="submit" class="h-11 rounded-xl bg-[#1e293b] px-6 text-sm font-bold text-white hover:bg-black transition-colors">
                        Buscar
                    </button>
                </form>

                <a href="{{ route('suppliers.create') }}"
                   class="h-11 flex items-center gap-2 rounded-xl bg-red-600 px-6 text-sm font-bold text-white hover:bg-red-700 shadow-md shadow-red-100 transition-all active:scale-95">
                    <span class="text-lg">+</span> Nuevo Proveedor
                </a>
            </div>
        </div>

        {{-- Tabla con Fondos de Colores Vivos y Letras Negras --}}
        <div class="overflow-x-auto bg-white">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr>
                        <th class="px-6 py-4 bg-orange-100 text-[11px] font-black uppercase tracking-wider text-black border-b-2 border-orange-200">
                            Nombre
                        </th>
                        <th class="px-6 py-4 bg-blue-100 text-[11px] font-black uppercase tracking-wider text-black border-b-2 border-blue-200">
                            NIT
                        </th>
                        <th class="px-6 py-4 bg-green-100 text-[11px] font-black uppercase tracking-wider text-black border-b-2 border-green-200 text-center">
                            Estado
                        </th>
                        <th class="px-6 py-4 bg-red-100 text-right text-[11px] font-black uppercase tracking-wider text-black border-b-2 border-red-200">
                            Acciones
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    @forelse($suppliers as $supplier)
                        <tr class="hover:bg-gray-50/80 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <span class="h-2 w-2 rounded-full bg-orange-500"></span>
                                    <span class="text-sm font-bold text-gray-900">{{ $supplier->name }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600 font-bold">
                                {{ $supplier->nit ?? '—' }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if($supplier->active)
                                    <span class="text-xs font-bold text-emerald-600">Activo</span>
                                @else
                                    <span class="text-xs font-bold text-gray-400">Inactivo</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex justify-end items-center gap-4">
                                    <form method="POST" action="{{ route('suppliers.toggle', $supplier) }}">
                                        @csrf @method('PATCH')
                                        <button type="submit" class="text-xs font-black uppercase {{ $supplier->active ? 'text-black hover:text-gray-600' : 'text-orange-600 hover:text-orange-700' }}">
                                            {{ $supplier->active ? 'Desactivar' : 'Activar' }}
                                        </button>
                                    </form>
                                    <a href="{{ route('suppliers.edit', $supplier) }}" class="text-xs font-black uppercase text-black hover:text-blue-700">Editar</a>
                                    <form method="POST" action="{{ route('suppliers.destroy', $supplier) }}" onsubmit="return confirm('¿Eliminar?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-xs font-black uppercase text-red-600 hover:text-red-700">Eliminar</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-16 text-center text-gray-400 font-bold bg-white">
                                No se encontraron proveedores registrados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection