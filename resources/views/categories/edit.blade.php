@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Editar Categoría" />

    <div class="max-w-xl">
        {{-- Card forzando fondo blanco y bordes redondeados --}}
        <div class="bg-white rounded-3xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="p-6 border-b border-gray-100">
                <h3 class="text-lg font-bold text-gray-900">Datos de la Categoría</h3>
            </div>

            <form method="POST" action="{{ route('categories.update', $category) }}" class="p-6 space-y-5">
                @csrf @method('PUT')

                <div>
                    <label class="mb-1.5 block text-sm font-bold text-gray-700">Nombre <span class="text-red-600">*</span></label>
                    <input
                        type="text"
                        name="name"
                        value="{{ old('name', $category->name) }}"
                        class="h-11 w-full rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 focus:border-orange-500 focus:ring-0 outline-none transition-all {{ $errors->has('name') ? 'border-red-600' : '' }}"
                        required
                    />
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-bold text-gray-700">Categoría Padre</label>
                    <div class="relative">
                        <select name="parent_id" class="h-11 w-full appearance-none rounded-xl border border-gray-300 bg-white px-4 py-2.5 pr-11 text-sm text-gray-800 focus:border-orange-500 focus:ring-0 outline-none">
                            <option value="">— Sin padre (Categoría Raíz) —</option>
                            @foreach($parents as $p)
                                @if($p->id !== $category->id)
                                    <option value="{{ $p->id }}" {{ old('parent_id', $category->parent_id) == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                                @endif
                            @endforeach
                        </select>
                        <div class="absolute inset-y-0 right-4 flex items-center pointer-events-none text-gray-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-3 pt-4 border-t border-gray-50">
                    <button type="submit" class="rounded-xl bg-orange-600 px-6 py-2.5 text-sm font-bold text-white hover:bg-orange-700 transition-all">Actualizar</button>
                    <a href="{{ route('categories.index') }}" class="rounded-xl bg-gray-100 px-6 py-2.5 text-sm font-bold text-gray-700 hover:bg-gray-200">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
@endsection