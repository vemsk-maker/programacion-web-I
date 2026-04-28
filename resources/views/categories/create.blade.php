@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Nueva Categoría" />

    <div class="max-w-xl">
        <x-common.component-card title="Datos de la Categoría">
            <form method="POST" action="{{ route('categories.store') }}" class="space-y-5">
                @csrf

                {{-- Name --}}
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                        Nombre <span class="text-error-500">*</span>
                    </label>
                    <input
                        type="text"
                        name="name"
                        value="{{ old('name') }}"
                        placeholder="Ej: Lácteos"
                        class="shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 {{ $errors->has('name') ? 'border-error-400 dark:border-error-500' : 'border-gray-300 dark:border-gray-700' }}"
                    />
                    @error('name')
                        <p class="mt-1 text-xs text-error-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Parent category --}}
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                        Categoría Padre
                    </label>
                    <div x-data="{ isOptionSelected: {{ old('parent_id') ? 'true' : 'false' }} }" class="relative z-20 bg-transparent">
                        <select
                            name="parent_id"
                            class="shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 pr-11 text-sm focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"
                            :class="isOptionSelected ? 'text-gray-800 dark:text-white/90' : 'text-gray-400 dark:text-gray-500'"
                            @change="isOptionSelected = true">
                            <option value="">— Sin padre (categoría raíz) —</option>
                            @foreach($parents as $parent)
                                <option value="{{ $parent->id }}" {{ old('parent_id') == $parent->id ? 'selected' : '' }}
                                    class="text-gray-700 dark:bg-gray-900 dark:text-gray-400">
                                    {{ $parent->name }}
                                </option>
                            @endforeach
                        </select>
                        <span class="pointer-events-none absolute top-1/2 right-4 z-30 -translate-y-1/2 text-gray-500 dark:text-gray-400">
                            <svg class="stroke-current" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M4.79175 7.396L10.0001 12.6043L15.2084 7.396" stroke="" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </span>
                    </div>
                    @error('parent_id')
                        <p class="mt-1 text-xs text-error-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Actions --}}
                <div class="flex items-center gap-3 pt-2">
                    <button type="submit"
                            class="rounded-lg bg-brand-500 px-5 py-2.5 text-sm font-medium text-white hover:bg-brand-600">
                        Guardar
                    </button>
                    <a href="{{ route('categories.index') }}"
                       class="rounded-lg bg-gray-100 px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-200 dark:bg-white/10 dark:text-white/80 dark:hover:bg-white/20">
                        Cancelar
                    </a>
                </div>
            </form>
        </x-common.component-card>
    </div>
@endsection
