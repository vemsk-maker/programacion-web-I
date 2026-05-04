@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Ubicaciones" />

    <div class="rounded-3xl border border-gray-200 bg-white shadow-sm overflow-hidden">

        {{-- Header --}}
        <div class="flex flex-wrap items-center justify-between gap-3 px-6 py-5 border-b border-gray-100">
            <div>
                <h3 class="text-xl font-bold text-gray-900">Sucursales y Almacenes</h3>
                <p class="text-sm text-gray-500 font-medium">Gestión de ubicaciones del sistema</p>
            </div>

            <div class="flex items-center gap-2">
                <form method="GET" action="{{ route('admin.locations.index') }}" class="flex items-center gap-2">
                    <input
                        type="text"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Buscar ubicación..."
                        class="h-11 w-64 rounded-xl border border-gray-200 bg-gray-50 px-4 py-2 text-sm text-gray-800 focus:border-red-500 focus:ring-0 outline-none"
                    />
                    <button type="submit" class="h-11 rounded-xl bg-[#1e293b] px-5 text-sm font-bold text-white hover:bg-black transition-colors">
                        Buscar
                    </button>
                </form>

                <a href="{{ route('admin.locations.create') }}"
                   class="h-11 flex items-center gap-2 rounded-xl bg-[#e11d48] px-6 text-sm font-bold text-white hover:bg-red-700 shadow-md shadow-red-100 transition-all active:scale-95">
                    <span class="text-lg leading-none">+</span> Nueva Ubicación
                </a>
            </div>
        </div>

        {{-- Flash messages --}}
        @if (session('success'))
            <div class="mx-6 mt-4 rounded-xl bg-green-50 border border-green-200 px-4 py-3 text-sm font-bold text-green-700">
                {{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div class="mx-6 mt-4 rounded-xl bg-red-50 border border-red-200 px-4 py-3 text-sm font-bold text-red-700">
                {{ session('error') }}
            </div>
        @endif

        {{-- Tabla --}}
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr>
                        <th class="px-6 py-4 bg-orange-50 text-[11px] font-black uppercase tracking-wider text-gray-700 border-b border-gray-100">Nombre</th>
                        <th class="px-6 py-4 bg-blue-50 text-[11px] font-black uppercase tracking-wider text-gray-700 border-b border-gray-100">Tipo</th>
                        <th class="px-6 py-4 bg-purple-50 text-[11px] font-black uppercase tracking-wider text-gray-700 border-b border-gray-100">Sub-ubicaciones</th>
                        <th class="px-6 py-4 bg-green-50 text-[11px] font-black uppercase tracking-wider text-gray-700 border-b border-gray-100 text-center">Estado</th>
                        <th class="px-6 py-4 bg-gray-50 text-[11px] font-black uppercase tracking-wider text-gray-700 border-b border-gray-100 text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse ($locations as $location)
                        @php
                            $typeColors = [
                                'store'     => 'bg-orange-100 text-orange-700',
                                'warehouse' => 'bg-blue-100 text-blue-700',
                                'waste'     => 'bg-gray-100 text-gray-600',
                            ];
                            $typeLabels = [
                                'store'     => 'Sucursal',
                                'warehouse' => 'Almacén',
                                'waste'     => 'Merma',
                            ];
                            $tv = $location->type->value;
                        @endphp
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-6 py-4 text-sm font-bold text-gray-900">{{ $location->name }}</td>
                            <td class="px-6 py-4">
                                <span class="inline-block rounded-full px-3 py-1 text-xs font-black {{ $typeColors[$tv] ?? 'bg-gray-100 text-gray-600' }}">
                                    {{ $typeLabels[$tv] ?? $tv }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                @if ($location->children->isEmpty())
                                    <span class="text-xs text-gray-400 italic">— ninguna —</span>
                                @else
                                    <div class="flex flex-wrap gap-1">
                                        @foreach ($location->children as $child)
                                            <span class="inline-flex items-center gap-1 rounded-full bg-blue-50 px-2 py-0.5 text-xs font-bold text-blue-700">
                                                {{ $child->name }}
                                                @unless ($child->active)
                                                    <span class="text-gray-400">(inact.)</span>
                                                @endunless
                                            </span>
                                        @endforeach
                                    </div>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center">
                                <form method="POST" action="{{ url('admin/locations/' . $location->id . '/toggle') }}" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit"
                                        class="inline-flex items-center rounded-full px-3 py-1 text-xs font-black transition-all
                                        {{ $location->active ? 'bg-green-100 text-green-700 hover:bg-green-200' : 'bg-gray-100 text-gray-500 hover:bg-gray-200' }}">
                                        {{ $location->active ? 'Activa' : 'Inactiva' }}
                                    </button>
                                </form>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="{{ route('admin.locations.edit', $location->id) }}"
                                       class="rounded-lg bg-blue-50 px-3 py-1.5 text-xs font-black text-blue-700 hover:bg-blue-100 transition-colors">
                                        Editar
                                    </a>
                                    <form method="POST" action="{{ route('admin.locations.destroy', $location->id) }}"
                                          onsubmit="return confirm('¿Eliminar la ubicación {{ addslashes($location->name) }}? Esta acción es irreversible.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="rounded-lg bg-red-50 px-3 py-1.5 text-xs font-black text-red-600 hover:bg-red-100 transition-colors">
                                            Eliminar
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-sm text-gray-400 font-bold">
                                No se encontraron ubicaciones.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Paginación --}}
        @if ($locations->hasPages())
            <div class="px-6 py-4 border-t border-gray-100">
                {{ $locations->links() }}
            </div>
        @endif
    </div>
@endsection
