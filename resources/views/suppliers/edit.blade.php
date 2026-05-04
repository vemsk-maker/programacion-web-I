@extends('layouts.app')

@section('content')
    <div class="mb-6 bg-white">
        <x-common.page-breadcrumb pageTitle="Editar Proveedor" />
    </div>

    <div class="max-w-xl">
        {{-- Contenedor Estética Ayma: Blanco, Bordes 3xl, Sin Modo Oscuro --}}
        <div class="bg-white rounded-3xl border border-gray-200 shadow-sm overflow-hidden">
            
            {{-- Encabezado del Card --}}
            <div class="p-6 border-b border-gray-100 bg-white">
                <h3 class="text-lg font-black text-gray-900 uppercase tracking-tight">Modificar Información</h3>
                <p class="text-xs text-gray-500 font-bold">Actualice los datos del proveedor seleccionado</p>
            </div>

            <form method="POST" action="{{ route('suppliers.update', $supplier) }}" class="p-6 space-y-5 bg-white">
                @csrf 
                @method('PUT')

                {{-- Nombre (Requerido) --}}
                <div>
                    <label class="mb-1.5 block text-sm font-black text-gray-700 uppercase">
                        Nombre <span class="text-red-600">*</span>
                    </label>
                    <input 
                        type="text" 
                        name="name" 
                        value="{{ old('name', $supplier->name) }}" 
                        placeholder="Nombre del proveedor"
                        class="h-11 w-full rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 font-bold focus:border-orange-500 focus:ring-0 outline-none transition-all {{ $errors->has('name') ? 'border-red-600' : '' }}" 
                        required 
                    />
                    @error('name')
                        <p class="mt-1 text-xs font-black text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- NIT (Opcional) --}}
                <div>
                    <label class="mb-1.5 block text-sm font-black text-gray-700 uppercase">NIT / Documento</label>
                    <input 
                        type="text" 
                        name="nit" 
                        value="{{ old('nit', $supplier->nit) }}" 
                        placeholder="Ej: 123456789"
                        class="h-11 w-full rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 font-bold focus:border-orange-500 focus:ring-0 outline-none transition-all" 
                    />
                    @error('nit')
                        <p class="mt-1 text-xs font-black text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Información de Contacto --}}
                <div>
                    <label class="mb-1.5 block text-sm font-black text-gray-700 uppercase">Contacto</label>
                    <textarea 
                        name="contact_info" 
                        rows="3" 
                        placeholder="Teléfono, email, dirección..."
                        class="w-full rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 font-bold focus:border-orange-500 focus:ring-0 outline-none transition-all"
                    >{{ old('contact_info', $supplier->contact_info) }}</textarea>
                    @error('contact_info')
                        <p class="mt-1 text-xs font-black text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Toggle de Estado (Naranja Ayma) --}}
                <div x-data="{ active: {{ old('active', $supplier->active) ? 'true' : 'false' }} }">
                    <label class="flex cursor-pointer items-center gap-3 text-sm font-black text-gray-700 select-none uppercase">
                        <div class="relative">
                            <input type="hidden" name="active" :value="active ? '1' : '0'" />
                            <input type="checkbox" class="sr-only" @change="active = !active" :checked="active" />
                            {{-- Color naranja para el switch activo --}}
                            <div class="block h-6 w-11 rounded-full transition-colors duration-300"
                                :class="active ? 'bg-orange-500' : 'bg-gray-200'"></div>
                            <div :class="active ? 'translate-x-full' : 'translate-x-0'"
                                class="absolute top-0.5 left-0.5 h-5 w-5 rounded-full bg-white shadow-sm duration-300 ease-linear"></div>
                        </div>
                        <span x-text="active ? 'Proveedor Activo' : 'Proveedor Inactivo'"></span>
                    </label>
                </div>

                {{-- Botones de Acción: Rojo para Actualizar --}}
                <div class="flex items-center gap-3 pt-4 border-t border-gray-50">
                    <button type="submit" 
                            class="rounded-xl bg-red-600 px-8 py-2.5 text-sm font-black text-white uppercase hover:bg-red-700 shadow-md shadow-red-100 transition-all active:scale-95">
                        Actualizar
                    </button>
                    <a href="{{ route('suppliers.index') }}" 
                       class="rounded-xl bg-gray-100 px-6 py-2.5 text-sm font-black text-gray-700 uppercase hover:bg-gray-200 transition-all text-center">
                        Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>
@endsection