@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Editar Proveedor" />

    <div class="max-w-xl">
        <x-common.component-card title="Datos del Proveedor">
            <form method="POST" action="{{ route('suppliers.update', $supplier) }}" class="space-y-5">
                @csrf @method('PUT')

                {{-- Name --}}
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                        Nombre <span class="text-error-500">*</span>
                    </label>
                    <input type="text" name="name" value="{{ old('name', $supplier->name) }}" placeholder="Nombre del proveedor"
                        class="shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 {{ $errors->has('name') ? 'border-error-400 dark:border-error-500' : 'border-gray-300 dark:border-gray-700' }}" />
                    @error('name')<p class="mt-1 text-xs text-error-500">{{ $message }}</p>@enderror
                </div>

                {{-- NIT --}}
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">NIT</label>
                    <input type="text" name="nit" value="{{ old('nit', $supplier->nit) }}" placeholder="Ej: 123456789"
                        class="shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30" />
                    @error('nit')<p class="mt-1 text-xs text-error-500">{{ $message }}</p>@enderror
                </div>

                {{-- Contact info --}}
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Contacto</label>
                    <textarea name="contact_info" rows="3" placeholder="Teléfono, email, dirección..."
                        class="shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30">{{ old('contact_info', $supplier->contact_info) }}</textarea>
                    @error('contact_info')<p class="mt-1 text-xs text-error-500">{{ $message }}</p>@enderror
                </div>

                {{-- Active toggle --}}
                <div x-data="{ active: {{ old('active', $supplier->active) ? 'true' : 'false' }} }">
                    <label class="flex cursor-pointer items-center gap-3 text-sm font-medium text-gray-700 select-none dark:text-gray-400">
                        <div class="relative">
                            <input type="hidden" name="active" :value="active ? '1' : '0'" />
                            <input type="checkbox" class="sr-only" @change="active = !active" :checked="active" />
                            <div class="block h-6 w-11 rounded-full"
                                :class="active ? 'bg-brand-500' : 'bg-gray-200 dark:bg-white/10'"></div>
                            <div :class="active ? 'translate-x-full' : 'translate-x-0'"
                                class="shadow-theme-sm absolute top-0.5 left-0.5 h-5 w-5 rounded-full bg-white duration-300 ease-linear"></div>
                        </div>
                        <span x-text="active ? 'Activo' : 'Inactivo'"></span>
                    </label>
                </div>

                {{-- Actions --}}
                <div class="flex items-center gap-3 pt-2">
                    <button type="submit" class="rounded-lg bg-brand-500 px-5 py-2.5 text-sm font-medium text-white hover:bg-brand-600">
                        Actualizar
                    </button>
                    <a href="{{ route('suppliers.index') }}" class="rounded-lg bg-gray-100 px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-200 dark:bg-white/10 dark:text-white/80 dark:hover:bg-white/20">
                        Cancelar
                    </a>
                </div>
            </form>
        </x-common.component-card>
    </div>
@endsection
