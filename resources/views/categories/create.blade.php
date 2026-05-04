@extends('layouts.app')

@section('content')
    <div class="mb-6 bg-white">
        <x-common.page-breadcrumb pageTitle="Nueva Categoría" />
    </div>

    <div class="max-w-xl">
        {{-- Contenedor con bordes redondeados amplios y fondo blanco --}}
        <div class="bg-white rounded-3xl border border-gray-200 shadow-sm overflow-hidden">
            
            {{-- Encabezado del Card --}}
            <div class="p-6 border-b border-gray-100">
                <h3 class="text-lg font-bold text-gray-900">Datos de la Categoría</h3>
                <p class="text-xs text-gray-500">Registra una nueva agrupación para tus productos</p>
            </div>

            <form method="POST" action="{{ route('categories.store') }}" class="p-6 space-y-5">
                @csrf

                {{-- Campo Nombre --}}
                <div>
                    <label class="mb-1.5 block text-sm font-bold text-gray-700">
                        Nombre <span class="text-red-600">*</span>
                    </label>
                    <input
                        type="text"
                        name="name"
                        value="{{ old('name') }}"
                        placeholder="Ej: Lácteos"
                        class="h-11 w-full rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:border-orange-500 focus:ring-0 outline-none transition-all {{ $errors->has('name') ? 'border-red-600' : '' }}"
                        required
                    />
                    @error('name')
                        <p class="mt-1 text-xs font-bold text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Campo Categoría Padre --}}
                <div>
                    <label class="mb-1.5 block text-sm font-bold text-gray-700">
                        Categoría Padre (Opcional)
                    </label>
                    <div class="relative">
                        <select 
                            name="parent_id" 
                            class="h-11 w-full appearance-none rounded-xl border border-gray-300 bg-white px-4 py-2.5 pr-11 text-sm text-gray-800 focus:border-orange-500 focus:ring-0 outline-none transition-all"
                        >
                            <option value="">— Sin padre (Categoría Raíz) —</option>
                            @foreach($parents as $parent)
                                <option value="{{ $parent->id }}" {{ old('parent_id') == $parent->id ? 'selected' : '' }}>
                                    {{ $parent->name }}
                                </option>
                            @endforeach
                        </select>
                        {{-- Icono de flecha personalizado --}}
                        <div class="absolute inset-y-0 right-4 flex items-center pointer-events-none text-gray-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </div>
                    </div>
                    @error('parent_id')
                        <p class="mt-1 text-xs font-bold text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Botones de Acción --}}
                <div class="flex items-center gap-3 pt-4 border-t border-gray-50">
                    <button type="submit" 
                            class="rounded-xl bg-red-600 px-8 py-2.5 text-sm font-bold text-white hover:bg-red-700 shadow-md shadow-red-100 transition-all active:scale-95">
                        Guardar
                    </button>
                    <a href="{{ route('categories.index') }}" 
                       class="rounded-xl bg-gray-100 px-6 py-2.5 text-sm font-bold text-gray-700 hover:bg-gray-200 transition-all text-center">
                        Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>
@endsection