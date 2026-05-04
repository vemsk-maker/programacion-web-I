@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Nuevo Producto" />

    <div class="max-w-2xl mx-auto">
        {{-- Forzamos fondo blanco puro y eliminamos sombras oscuras --}}
        <div class="rounded-3xl bg-white p-8 border border-gray-100 shadow-sm">
            <h3 class="text-lg font-black text-[#1e293b] mb-6">Datos del Producto</h3>

            <form method="POST" action="{{ route('products.store') }}" class="space-y-6"
                  x-data="{
                      useBatches: {{ old('use_batches', false) ? 'true' : 'false' }},
                      active: {{ old('active', true) ? 'true' : 'false' }},
                      barcodes: {{ json_encode(old('barcodes', [['barcode' => '', 'units_per_scan' => 1]])) }},
                      addBarcode() { this.barcodes.push({ barcode: '', units_per_scan: 1 }) },
                      removeBarcode(i) { if (this.barcodes.length > 1) this.barcodes.splice(i, 1) }
                  }">
                @csrf

                {{-- Nombre --}}
                <div>
                    <label class="mb-2 block text-[11px] font-black uppercase tracking-widest text-[#1e293b]/70">
                        Nombre <span class="text-[#e11d48]">*</span>
                    </label>
                    <input type="text" name="name" value="{{ old('name') }}" placeholder="Nombre del producto"
                        class="h-12 w-full rounded-2xl border border-gray-200 bg-white px-5 text-sm font-bold text-[#1e293b] focus:border-[#e11d48] focus:ring-4 focus:ring-[#e11d48]/5 transition-all outline-none" />
                </div>

                {{-- Descripción --}}
                <div>
                    <label class="mb-2 block text-[11px] font-black uppercase tracking-widest text-[#1e293b]/70">Descripción</label>
                    <textarea name="description" rows="2" placeholder="Descripción opcional..."
                        class="w-full rounded-2xl border border-gray-200 bg-white px-5 py-3 text-sm font-medium text-[#1e293b] focus:border-[#e11d48] focus:ring-4 focus:ring-[#e11d48]/5 transition-all outline-none">{{ old('description') }}</textarea>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    {{-- Categoría --}}
                    <div>
                        <label class="mb-2 block text-[11px] font-black uppercase tracking-widest text-[#1e293b]/70">
                            Categoría <span class="text-[#e11d48]">*</span>
                        </label>
                        <select name="category_id" class="h-12 w-full rounded-2xl border border-gray-200 bg-white px-5 text-sm font-bold text-[#1e293b] focus:border-[#e11d48] outline-none">
                            <option value="">Seleccionar...</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Unidad --}}
                    <div>
                        <label class="mb-2 block text-[11px] font-black uppercase tracking-widest text-[#1e293b]/70">
                            Medida <span class="text-[#e11d48]">*</span>
                        </label>
                        <input type="text" name="unit_of_measure" placeholder="Ej: Unidad"
                            class="h-12 w-full rounded-2xl border border-gray-200 bg-white px-5 text-sm font-bold text-[#1e293b] focus:border-[#e11d48] outline-none" />
                    </div>
                </div>

                {{-- Toggles --}}
                <div class="flex items-center gap-8 py-2">
                    <label class="flex items-center gap-3 cursor-pointer select-none">
                        <div class="relative">
                            <input type="checkbox" class="sr-only" @change="useBatches = !useBatches" :checked="useBatches" />
                            <div class="block h-7 w-12 rounded-full transition-colors" :class="useBatches ? 'bg-[#e11d48]' : 'bg-gray-200'"></div>
                            <div :class="useBatches ? 'translate-x-5' : 'translate-x-0'" class="absolute top-1 left-1 h-5 w-5 rounded-full bg-white transition-transform shadow-sm"></div>
                        </div>
                        <span class="text-[10px] font-black uppercase tracking-widest text-[#1e293b]">Lotes</span>
                    </label>

                    <label class="flex items-center gap-3 cursor-pointer select-none">
                        <div class="relative">
                            <input type="checkbox" class="sr-only" @change="active = !active" :checked="active" />
                            <div class="block h-7 w-12 rounded-full transition-colors" :class="active ? 'bg-emerald-500' : 'bg-gray-200'"></div>
                            <div :class="active ? 'translate-x-5' : 'translate-x-0'" class="absolute top-1 left-1 h-5 w-5 rounded-full bg-white transition-transform shadow-sm"></div>
                        </div>
                        <span class="text-[10px] font-black uppercase tracking-widest text-[#1e293b]" x-text="active ? 'Activo' : 'Inactivo'"></span>
                    </label>
                </div>

                {{-- Sección Códigos (Gris muy claro, NO azul oscuro) --}}
                <div class="rounded-3xl bg-gray-50 p-6 border border-gray-100">
                    <div class="mb-4 flex items-center justify-between">
                        <label class="text-[11px] font-black uppercase tracking-widest text-[#1e293b]/70">Códigos de Barras</label>
                        <button type="button" @click="addBarcode()"
                            class="rounded-xl bg-[#1e293b] px-4 py-2 text-[10px] font-black uppercase tracking-widest text-white hover:bg-black transition-all">
                            + Añadir
                        </button>
                    </div>
                    
                    <div class="space-y-3">
                        <template x-for="(barcode, i) in barcodes" :key="i">
                            <div class="flex items-center gap-2">
                                <input type="text" :name="`barcodes[${i}][barcode]`" x-model="barcode.barcode" placeholder="Escanear..."
                                    class="h-11 flex-1 rounded-xl border border-gray-200 bg-white px-4 text-sm font-bold text-[#1e293b] focus:border-[#e11d48] outline-none" />
                                <input type="number" :name="`barcodes[${i}][units_per_scan]`" x-model.number="barcode.units_per_scan"
                                    class="h-11 w-16 rounded-xl border border-gray-200 bg-white text-center text-sm font-black text-[#e11d48] outline-none" />
                                <button type="button" @click="removeBarcode(i)"
                                    class="flex h-11 w-11 items-center justify-center rounded-xl bg-red-50 text-[#e11d48] hover:bg-[#e11d48] hover:text-white transition-all">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M18 6L6 18M6 6l12 12"/></svg>
                                </button>
                            </div>
                        </template>
                    </div>
                </div>

                {{-- Botones --}}
                <div class="flex items-center gap-4 pt-4">
                    <button type="submit" class="flex-1 h-14 rounded-2xl bg-[#e11d48] text-[11px] font-black uppercase tracking-[2px] text-white shadow-lg shadow-red-500/20 hover:bg-[#be123c] transition-all active:scale-[0.98]">
                        Guardar Producto
                    </button>
                    <a href="{{ route('products.index') }}" class="h-14 px-8 flex items-center rounded-2xl bg-gray-100 text-[11px] font-black uppercase tracking-widest text-gray-500 hover:bg-gray-200 transition-all">
                        Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>
@endsection