@extends('layouts.app')

@section('content')
    <div class="mb-6">
        <x-common.page-breadcrumb pageTitle="Nueva Ubicación" />
    </div>

    <div class="max-w-xl"
         x-data="{
             locType: '{{ old('type', 'store') }}',
             createWarehouse: false
         }">
        <div class="bg-white rounded-3xl border border-gray-200 shadow-sm overflow-hidden">

            <div class="p-6 border-b border-gray-100">
                <h3 class="text-lg font-black text-gray-900 uppercase tracking-tight">Nueva Ubicación</h3>
                <p class="text-xs text-gray-500 font-bold">Registre una sucursal, almacén u otro tipo de ubicación</p>
            </div>

            <form method="POST" action="{{ route('admin.locations.store') }}" class="p-6 space-y-5">
                @csrf

                {{-- Nombre --}}
                <div>
                    <label class="mb-1.5 block text-sm font-black text-gray-700 uppercase">
                        Nombre <span class="text-red-600">*</span>
                    </label>
                    <input type="text" name="name" value="{{ old('name') }}"
                        placeholder="Ej: Sucursal Centro, Almacén Norte..."
                        class="h-11 w-full rounded-xl border {{ $errors->has('name') ? 'border-red-500' : 'border-gray-300' }} bg-white px-4 py-2.5 text-sm text-gray-800 font-bold focus:border-red-500 focus:ring-0 outline-none"
                        required />
                    @error('name')
                        <p class="mt-1 text-xs font-black text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Tipo --}}
                <div>
                    <label class="mb-1.5 block text-sm font-black text-gray-700 uppercase">
                        Tipo <span class="text-red-600">*</span>
                    </label>
                    <div class="flex gap-3">
                        <label class="flex-1 cursor-pointer" x-on:click="locType = 'store'; createWarehouse = false">
                            <input type="radio" name="type" value="store" class="sr-only"
                                {{ old('type', 'store') === 'store' ? 'checked' : '' }} />
                            <div class="rounded-xl border-2 p-3 text-center transition-all"
                                :class="locType === 'store' ? 'border-red-500 bg-red-50' : 'border-gray-200 bg-gray-50 hover:border-gray-300'">
                                <div class="text-xl mb-1">🏪</div>
                                <div class="text-xs font-black text-gray-700 uppercase">Sucursal</div>
                                <div class="text-[10px] text-gray-500">Tienda física</div>
                            </div>
                        </label>
                        <label class="flex-1 cursor-pointer" x-on:click="locType = 'warehouse'; createWarehouse = false">
                            <input type="radio" name="type" value="warehouse" class="sr-only"
                                {{ old('type') === 'warehouse' ? 'checked' : '' }} />
                            <div class="rounded-xl border-2 p-3 text-center transition-all"
                                :class="locType === 'warehouse' ? 'border-blue-500 bg-blue-50' : 'border-gray-200 bg-gray-50 hover:border-gray-300'">
                                <div class="text-xl mb-1">🏭</div>
                                <div class="text-xs font-black text-gray-700 uppercase">Almacén</div>
                                <div class="text-[10px] text-gray-500">Depósito de stock</div>
                            </div>
                        </label>
                        <label class="flex-1 cursor-pointer" x-on:click="locType = 'waste'; createWarehouse = false">
                            <input type="radio" name="type" value="waste" class="sr-only"
                                {{ old('type') === 'waste' ? 'checked' : '' }} />
                            <div class="rounded-xl border-2 p-3 text-center transition-all"
                                :class="locType === 'waste' ? 'border-gray-500 bg-gray-100' : 'border-gray-200 bg-gray-50 hover:border-gray-300'">
                                <div class="text-xl mb-1">🗑️</div>
                                <div class="text-xs font-black text-gray-700 uppercase">Merma</div>
                                <div class="text-[10px] text-gray-500">Productos perdidos</div>
                            </div>
                        </label>
                    </div>
                    @error('type')
                        <p class="mt-1 text-xs font-black text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Sucursal padre (si es almacén) --}}
                <div x-show="locType === 'warehouse'" x-transition>
                    <label class="mb-1.5 block text-sm font-black text-gray-700 uppercase">
                        Sucursal asociada (opcional)
                    </label>
                    <select name="parent_id"
                        class="h-11 w-full rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 font-bold focus:border-red-500 focus:ring-0 outline-none">
                        <option value="">— Sin sucursal padre —</option>
                        @foreach ($stores as $store)
                            <option value="{{ $store->id }}" {{ old('parent_id') == $store->id ? 'selected' : '' }}>
                                {{ $store->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('parent_id')
                        <p class="mt-1 text-xs font-black text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Crear almacén automático (solo si es sucursal) --}}
                <div x-show="locType === 'store'" x-transition
                     class="rounded-xl border border-dashed border-orange-300 bg-orange-50 p-4 space-y-3">
                    <label class="flex items-center gap-2 cursor-pointer text-sm font-black text-gray-700 select-none uppercase">
                        <input type="checkbox" name="create_warehouse" value="1"
                            @change="createWarehouse = $event.target.checked"
                            {{ old('create_warehouse') ? 'checked' : '' }}
                            class="rounded border-gray-300 text-red-600 focus:ring-red-500" />
                        Crear almacén automáticamente para esta sucursal
                    </label>

                    <div x-show="createWarehouse" x-transition>
                        <label class="mb-1.5 block text-xs font-black text-gray-600 uppercase">
                            Nombre del almacén (opcional)
                        </label>
                        <input type="text" name="warehouse_name" value="{{ old('warehouse_name') }}"
                            placeholder="Se usará 'Almacén [nombre sucursal]' por defecto"
                            class="h-10 w-full rounded-xl border border-gray-300 bg-white px-4 py-2 text-sm text-gray-800 font-bold focus:border-red-500 focus:ring-0 outline-none" />
                    </div>
                </div>

                {{-- Estado activo --}}
                <div x-data="{ active: true }">
                    <label class="flex cursor-pointer items-center gap-3 text-sm font-black text-gray-700 select-none uppercase">
                        <div class="relative">
                            <input type="hidden" name="active" :value="active ? '1' : '0'" />
                            <input type="checkbox" class="sr-only" @change="active = !active" :checked="active" />
                            <div class="block h-6 w-11 rounded-full transition-colors duration-300"
                                :class="active ? 'bg-red-500' : 'bg-gray-200'"></div>
                            <div :class="active ? 'translate-x-full' : 'translate-x-0'"
                                class="absolute top-0.5 left-0.5 h-5 w-5 rounded-full bg-white shadow-sm duration-300 ease-linear"></div>
                        </div>
                        <span x-text="active ? 'Ubicación Activa' : 'Ubicación Inactiva'"></span>
                    </label>
                </div>

                {{-- Botones --}}
                <div class="flex items-center gap-3 pt-4 border-t border-gray-100">
                    <button type="submit"
                        class="rounded-xl bg-[#e11d48] px-8 py-2.5 text-sm font-black text-white uppercase hover:bg-red-700 shadow-md shadow-red-100 transition-all active:scale-95">
                        Crear Ubicación
                    </button>
                    <a href="{{ route('admin.locations.index') }}"
                       class="rounded-xl bg-gray-100 px-6 py-2.5 text-sm font-black text-gray-700 uppercase hover:bg-gray-200 transition-all">
                        Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>
@endsection
