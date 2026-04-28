@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Proveedores" />

    {{-- Flash messages --}}
    @if(session('success'))
        <div class="mb-4 rounded-lg bg-success-50 border border-success-200 px-4 py-3 text-sm text-success-700 dark:bg-success-500/10 dark:border-success-500/20 dark:text-success-400">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="mb-4 rounded-lg bg-error-50 border border-error-200 px-4 py-3 text-sm text-error-700 dark:bg-error-500/10 dark:border-error-500/20 dark:text-error-400">
            {{ session('error') }}
        </div>
    @endif

    <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
        {{-- Header --}}
        <div class="flex flex-wrap items-center justify-between gap-3 px-6 py-4 border-b border-gray-100 dark:border-gray-800">
            <h3 class="text-base font-medium text-gray-800 dark:text-white/90">Lista de Proveedores</h3>
            <div class="flex items-center gap-3">
                <form method="GET" action="{{ route('suppliers.index') }}" class="flex items-center gap-2">
                    <input
                        type="text"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Buscar proveedor..."
                        class="shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-10 rounded-lg border border-gray-300 bg-transparent px-3 py-2 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30"
                    />
                    <button type="submit" class="flex h-10 items-center gap-1.5 rounded-lg bg-gray-100 px-3 text-sm font-medium text-gray-700 hover:bg-gray-200 dark:bg-white/10 dark:text-white/80 dark:hover:bg-white/20">
                        Buscar
                    </button>
                </form>
                <a href="{{ route('suppliers.create') }}"
                   class="flex items-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600">
                    + Nuevo Proveedor
                </a>
            </div>
        </div>

        {{-- Table --}}
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 dark:border-gray-800">
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Nombre</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">NIT</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Estado</th>
                        <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($suppliers as $supplier)
                        <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.02]">
                            <td class="px-6 py-3 font-medium text-gray-800 dark:text-white/90">
                                {{ $supplier->name }}
                            </td>
                            <td class="px-6 py-3 text-gray-500 dark:text-gray-400">
                                {{ $supplier->nit ?? '—' }}
                            </td>
                            <td class="px-6 py-3">
                                @if($supplier->active)
                                    <span class="inline-flex items-center rounded-full bg-success-50 px-2.5 py-0.5 text-xs font-medium text-success-700 dark:bg-success-500/10 dark:text-success-400">
                                        Activo
                                    </span>
                                @else
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-600 dark:bg-white/10 dark:text-gray-400">
                                        Inactivo
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-3 text-right">
                                <div class="flex justify-end gap-2">
                                    {{-- Toggle activo/inactivo --}}
                                    <form method="POST" action="{{ route('suppliers.toggle', $supplier) }}">
                                        @csrf @method('PATCH')
                                        <button type="submit"
                                                class="rounded-lg px-3 py-1.5 text-xs font-medium {{ $supplier->active ? 'bg-warning-50 text-warning-700 hover:bg-warning-100 dark:bg-warning-500/10 dark:text-warning-400' : 'bg-success-50 text-success-700 hover:bg-success-100 dark:bg-success-500/10 dark:text-success-400' }}">
                                            {{ $supplier->active ? 'Desactivar' : 'Activar' }}
                                        </button>
                                    </form>
                                    <a href="{{ route('suppliers.edit', $supplier) }}"
                                       class="rounded-lg bg-gray-100 px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-200 dark:bg-white/10 dark:text-white/80 dark:hover:bg-white/20">
                                        Editar
                                    </a>
                                    <form method="POST" action="{{ route('suppliers.destroy', $supplier) }}"
                                          onsubmit="return confirm('¿Eliminar este proveedor?')">
                                        @csrf @method('DELETE')
                                        <button type="submit"
                                                class="rounded-lg bg-error-50 px-3 py-1.5 text-xs font-medium text-error-600 hover:bg-error-100 dark:bg-error-500/10 dark:text-error-400 dark:hover:bg-error-500/20">
                                            Eliminar
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-10 text-center text-sm text-gray-400 dark:text-gray-500">
                                No hay proveedores registrados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($suppliers->hasPages())
            <div class="border-t border-gray-100 px-6 py-4 dark:border-gray-800">
                {{ $suppliers->links() }}
            </div>
        @endif
    </div>
@endsection
