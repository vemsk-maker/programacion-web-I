@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Nuevo Producto" />

    <div class="max-w-2xl">
        <x-common.component-card title="Datos del Producto">
            <form method="POST" action="{{ route('products.store') }}" class="space-y-5"
                  x-data="{
                      useBatches: {{ old('use_batches', false) ? 'true' : 'false' }},
                      active: {{ old('active', true) ? 'true' : 'false' }},
                      barcodes: {{ json_encode(old('barcodes', [['barcode' => '', 'units_per_scan' => 1]])) }},
                      addBarcode() { this.barcodes.push({ barcode: '', units_per_scan: 1 }) },
                      removeBarcode(i) { if (this.barcodes.length > 1) this.barcodes.splice(i, 1) }
                  }">
                @csrf

                {{-- Name --}}
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                        Nombre <span class="text-error-500">*</span>
                    </label>
                    <input type="text" name="name" value="{{ old('name') }}" placeholder="Nombre del producto"
                        class="shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 {{ $errors->has('name') ? 'border-error-400 dark:border-error-500' : 'border-gray-300 dark:border-gray-700' }}" />
                    @error('name')<p class="mt-1 text-xs text-error-500">{{ $message }}</p>@enderror
                </div>

                {{-- Description --}}
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Descripción</label>
                    <textarea name="description" rows="3" placeholder="Descripción opcional..."
                        class="shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30">{{ old('description') }}</textarea>
                </div>

                {{-- Category --}}
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                        Categoría <span class="text-error-500">*</span>
                    </label>
                    <div x-data="{ isOptionSelected: {{ old('category_id') ? 'true' : 'false' }} }" class="relative z-20 bg-transparent">
                        <select name="category_id"
                            class="shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full appearance-none rounded-lg border bg-transparent px-4 py-2.5 pr-11 text-sm focus:ring-3 focus:outline-hidden dark:bg-gray-900 dark:text-white/90 {{ $errors->has('category_id') ? 'border-error-400 dark:border-error-500' : 'border-gray-300 dark:border-gray-700' }}"
                            :class="isOptionSelected ? 'text-gray-800 dark:text-white/90' : 'text-gray-400 dark:text-gray-500'"
                            @change="isOptionSelected = true">
                            <option value="">— Seleccionar categoría —</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}
                                    class="text-gray-700 dark:bg-gray-900 dark:text-gray-400">
                                    {{ $category->name }}{{ $category->parent ? ' (' . $category->parent->name . ')' : '' }}
                                </option>
                            @endforeach
                        </select>
                        <span class="pointer-events-none absolute top-1/2 right-4 z-30 -translate-y-1/2 text-gray-500 dark:text-gray-400">
                            <svg class="stroke-current" width="20" height="20" viewBox="0 0 20 20" fill="none"><path d="M4.79175 7.396L10.0001 12.6043L15.2084 7.396" stroke="" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </span>
                    </div>
                    @error('category_id')<p class="mt-1 text-xs text-error-500">{{ $message }}</p>@enderror
                </div>

                {{-- Unit of measure --}}
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                        Unidad de Medida <span class="text-error-500">*</span>
                    </label>
                    <input type="text" name="unit_of_measure" value="{{ old('unit_of_measure') }}"
                        placeholder="Ej: unidad, kg, litro, caja, bolsa"
                        class="shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 {{ $errors->has('unit_of_measure') ? 'border-error-400 dark:border-error-500' : 'border-gray-300 dark:border-gray-700' }}" />
                    @error('unit_of_measure')<p class="mt-1 text-xs text-error-500">{{ $message }}</p>@enderror
                </div>

                {{-- Toggles --}}
                <div class="flex flex-wrap gap-8">
                    {{-- use_batches toggle --}}
                    <div>
                        <label class="flex cursor-pointer items-center gap-3 text-sm font-medium text-gray-700 select-none dark:text-gray-400">
                            <div class="relative">
                                <input type="hidden" name="use_batches" :value="useBatches ? '1' : '0'" />
                                <input type="checkbox" class="sr-only" @change="useBatches = !useBatches" :checked="useBatches" />
                                <div class="block h-6 w-11 rounded-full" :class="useBatches ? 'bg-brand-500' : 'bg-gray-200 dark:bg-white/10'"></div>
                                <div :class="useBatches ? 'translate-x-full' : 'translate-x-0'" class="shadow-theme-sm absolute top-0.5 left-0.5 h-5 w-5 rounded-full bg-white duration-300 ease-linear"></div>
                            </div>
                            Control por lotes (PEPS)
                        </label>
                        <p class="mt-1 ml-14 text-xs text-gray-400 dark:text-gray-500">Activa el seguimiento por lote y vencimiento</p>
                    </div>

                    {{-- active toggle --}}
                    <div>
                        <label class="flex cursor-pointer items-center gap-3 text-sm font-medium text-gray-700 select-none dark:text-gray-400">
                            <div class="relative">
                                <input type="hidden" name="active" :value="active ? '1' : '0'" />
                                <input type="checkbox" class="sr-only" @change="active = !active" :checked="active" />
                                <div class="block h-6 w-11 rounded-full" :class="active ? 'bg-brand-500' : 'bg-gray-200 dark:bg-white/10'"></div>
                                <div :class="active ? 'translate-x-full' : 'translate-x-0'" class="shadow-theme-sm absolute top-0.5 left-0.5 h-5 w-5 rounded-full bg-white duration-300 ease-linear"></div>
                            </div>
                            <span x-text="active ? 'Activo' : 'Inactivo'"></span>
                        </label>
                    </div>
                </div>

                {{-- Barcodes section --}}
                <div>
                    <div class="mb-3 flex items-center justify-between">
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-400">Códigos de Barras</label>
                        <button type="button" @click="addBarcode()"
                            class="rounded-lg bg-gray-100 px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-200 dark:bg-white/10 dark:text-white/80 dark:hover:bg-white/20">
                            + Agregar código
                        </button>
                    </div>
                    <div class="space-y-3">
                        <template x-for="(barcode, i) in barcodes" :key="i">
                            <div class="flex items-center gap-3">
                                <input type="text" :name="`barcodes[${i}][barcode]`" x-model="barcode.barcode"
                                    placeholder="Código de barras"
                                    class="shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-10 flex-1 rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30" />
                                <input type="number" :name="`barcodes[${i}][units_per_scan]`" x-model.number="barcode.units_per_scan"
                                    min="1" placeholder="Uds"
                                    class="shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-10 w-20 rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
                                <button type="button" @click="removeBarcode(i)"
                                    class="h-10 rounded-lg bg-error-50 px-3 text-xs font-medium text-error-600 hover:bg-error-100 dark:bg-error-500/10 dark:text-error-400"
                                    :disabled="barcodes.length === 1">
                                    ✕
                                </button>
                            </div>
                        </template>
                    </div>
                    <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">El campo "Uds" indica cuántas unidades representa un escaneo de ese código.</p>
                </div>

                {{-- Actions --}}
                <div class="flex items-center gap-3 pt-2">
                    <button type="submit" class="rounded-lg bg-brand-500 px-5 py-2.5 text-sm font-medium text-white hover:bg-brand-600">
                        Guardar
                    </button>
                    <a href="{{ route('products.index') }}" class="rounded-lg bg-gray-100 px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-200 dark:bg-white/10 dark:text-white/80 dark:hover:bg-white/20">
                        Cancelar
                    </a>
                </div>
            </form>
        </x-common.component-card>
    </div>
@endsection
