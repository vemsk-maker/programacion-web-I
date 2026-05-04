@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Editar Producto" />

    <div class="max-w-2xl">
        {{-- Contenedor principal con estética limpia Ayma --}}
        <div class="rounded-3xl border border-gray-200 bg-white shadow-sm p-8">
            <div class="mb-6">
                <h3 class="text-xl font-bold text-[#1e293b]">Actualizar Información</h3>
                <p class="text-sm text-gray-500">Modifique los campos necesarios para actualizar el artículo.</p>
            </div>

            <form method="POST" action="{{ route('products.update', $product) }}" class="space-y-6"
                  x-data="{
                      useBatches: {{ old('use_batches', $product->use_batches) ? 'true' : 'false' }},
                      active: {{ old('active', $product->active) ? 'true' : 'false' }},
                      barcodes: {{ json_encode(
                          old('barcodes', $product->barcodes->map(fn($b) => ['barcode' => $b->barcode, 'units_per_scan' => $b->units_per_scan])->toArray())
                          ?: [['barcode' => '', 'units_per_scan' => 1]]
                      ) }},
                      addBarcode() { this.barcodes.push({ barcode: '', units_per_scan: 1 }) },
                      removeBarcode(i) { if (this.barcodes.length > 1) this.barcodes.splice(i, 1) }
                  }">
                @csrf 
                @method('PUT')

                {{-- Name --}}
                <div>
                    <label class="mb-2 block text-sm font-bold text-[#1e293b]">
                        Nombre del Producto <span class="text-[#e11d48]">*</span>
                    </label>
                    <input type="text" name="name" value="{{ old('name', $product->name) }}" 
                        placeholder="Ej: Coca Cola 2L"
                        class="h-11 w-full rounded-xl border px-4 text-sm text-gray-800 placeholder:text-gray-400 focus:border-gray-400 focus:outline-none transition-all {{ $errors->has('name') ? 'border-red-400' : 'border-gray-200' }}" />
                    @error('name')<p class="mt-1.5 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
                </div>

                {{-- Description --}}
                <div>
                    <label class="mb-2 block text-sm font-bold text-[#1e293b]">Descripción</label>
                    <textarea name="description" rows="2" placeholder="Notas adicionales..."
                        class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm text-gray-800 placeholder:text-gray-400 focus:border-gray-400 focus:outline-none transition-all">{{ old('description', $product->description) }}</textarea>
                </div>

                {{-- Category & Unit --}}
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <div>
                        <label class="mb-2 block text-sm font-bold text-[#1e293b]">
                            Categoría <span class="text-[#e11d48]">*</span>
                        </label>
                        <div class="relative">
                            <select name="category_id"
                                class="h-11 w-full appearance-none rounded-xl border bg-white px-4 pr-11 text-sm text-gray-800 focus:border-gray-400 focus:outline-none transition-all {{ $errors->has('category_id') ? 'border-red-400' : 'border-gray-200' }}">
                                <option value="">— Seleccionar —</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ old('category_id', $product->category_id) == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}{{ $category->parent ? ' (' . $category->parent->name . ')' : '' }}
                                    </option>
                                @endforeach
                            </select>
                            <span class="pointer-events-none absolute top-1/2 right-4 -translate-y-1/2 text-gray-400">
                                <svg width="18" height="18" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M4.79175 7.396L10.0001 12.6043L15.2084 7.396" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            </span>
                        </div>
                        @error('category_id')<p class="mt-1.5 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-bold text-[#1e293b]">
                            Unidad de Medida <span class="text-[#e11d48]">*</span>
                        </label>
                        <input type="text" name="unit_of_measure" value="{{ old('unit_of_measure', $product->unit_of_measure) }}"
                            placeholder="Ej: unidad, kg"
                            class="h-11 w-full rounded-xl border px-4 text-sm text-gray-800 placeholder:text-gray-400 focus:border-gray-400 focus:outline-none transition-all {{ $errors->has('unit_of_measure') ? 'border-red-400' : 'border-gray-200' }}" />
                        @error('unit_of_measure')<p class="mt-1.5 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
                    </div>
                </div>

                {{-- Toggles --}}
                <div class="flex flex-wrap gap-8 py-2">
                    <div>
                        <label class="flex cursor-pointer items-center gap-3 text-sm font-bold text-[#1e293b] select-none">
                            <div class="relative">
                                <input type="hidden" name="use_batches" :value="useBatches ? '1' : '0'" />
                                <input type="checkbox" class="sr-only" @change="useBatches = !useBatches" :checked="useBatches" />
                                <div class="block h-6 w-11 rounded-full border border-gray-200 transition-colors" :class="useBatches ? 'bg-[#1e293b]' : 'bg-gray-100'"></div>
                                <div :class="useBatches ? 'translate-x-5' : 'translate-x-0'" class="absolute top-1 left-1 h-4 w-4 rounded-full bg-white shadow-sm transition-transform duration-200"></div>
                            </div>
                            Control PEPS (Lotes)
                        </label>
                    </div>

                    <div>
                        <label class="flex cursor-pointer items-center gap-3 text-sm font-bold text-[#1e293b] select-none">
                            <div class="relative">
                                <input type="hidden" name="active" :value="active ? '1' : '0'" />
                                <input type="checkbox" class="sr-only" @change="active = !active" :checked="active" />
                                <div class="block h-6 w-11 rounded-full border border-gray-200 transition-colors" :class="active ? 'bg-emerald-500 border-emerald-600' : 'bg-gray-100'"></div>
                                <div :class="active ? 'translate-x-5' : 'translate-x-0'" class="absolute top-1 left-1 h-4 w-4 rounded-full bg-white shadow-sm transition-transform duration-200"></div>
                            </div>
                            <span x-text="active ? 'Producto Activo' : 'Producto Inactivo'"></span>
                        </label>
                    </div>
                </div>

                {{-- Barcodes section --}}
                <div class="rounded-2xl border border-gray-100 bg-gray-50/50 p-5">
                    <div class="mb-4 flex items-center justify-between">
                        <label class="text-sm font-bold text-[#1e293b]">Códigos de Barras Vinculados</label>
                        <button type="button" @click="addBarcode()"
                            class="rounded-lg bg-white border border-gray-200 px-3 py-1.5 text-xs font-bold text-gray-600 hover:bg-gray-50 transition-colors shadow-sm">
                            + Añadir Código
                        </button>
                    </div>
                    <div class="space-y-3">
                        <template x-for="(barcode, i) in barcodes" :key="i">
                            <div class="flex items-center gap-3">
                                <div class="relative flex-1">
                                    <input type="text" :name="`barcodes[${i}][barcode]`" x-model="barcode.barcode"
                                        placeholder="Escanee o escriba el código"
                                        class="h-10 w-full rounded-xl border border-gray-200 bg-white px-4 text-sm text-gray-800 focus:border-gray-400 focus:outline-none" />
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="text-[10px] font-bold text-gray-400 uppercase">Uds:</span>
                                    <input type="number" :name="`barcodes[${i}][units_per_scan]`" x-model.number="barcode.units_per_scan"
                                        min="1"
                                        class="h-10 w-16 rounded-xl border border-gray-200 bg-white px-2 text-center text-sm font-bold text-gray-800 focus:border-gray-400 focus:outline-none" />
                                </div>
                                <button type="button" @click="removeBarcode(i)"
                                    class="flex h-10 w-10 items-center justify-center rounded-xl bg-red-50 text-red-500 hover:bg-red-100 transition-colors"
                                    :disabled="barcodes.length === 1">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                                </button>
                            </div>
                        </template>
                    </div>
                </div>

                {{-- Actions --}}
                <div class="flex items-center gap-4 pt-4">
                    <button type="submit" class="flex-1 h-12 rounded-xl bg-[#e11d48] text-sm font-bold text-white shadow-md hover:bg-[#be123c] transition-all transform active:scale-[0.98]">
                        Guardar Cambios
                    </button>
                    <a href="{{ route('products.show', $product) }}" class="flex-1 h-12 flex items-center justify-center rounded-xl bg-gray-100 text-sm font-bold text-gray-600 hover:bg-gray-200 transition-all">
                        Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>
@endsection