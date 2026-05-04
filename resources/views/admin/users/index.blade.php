@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Usuarios" />

    <div class="rounded-3xl border border-gray-200 bg-white shadow-sm overflow-hidden">

        {{-- Header --}}
        <div class="flex flex-wrap items-center justify-between gap-3 px-6 py-5 border-b border-gray-100">
            <div>
                <h3 class="text-xl font-bold text-gray-900">Gestión de Usuarios</h3>
                <p class="text-sm text-gray-500 font-medium">Cuentas de acceso al sistema</p>
            </div>

            <div class="flex items-center gap-2">
                <form method="GET" action="{{ route('admin.users.index') }}" class="flex items-center gap-2">
                    <input
                        type="text"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Buscar usuario..."
                        class="h-11 w-64 rounded-xl border border-gray-200 bg-gray-50 px-4 py-2 text-sm text-gray-800 focus:border-red-500 focus:ring-0 outline-none"
                    />
                    <button type="submit" class="h-11 rounded-xl bg-[#1e293b] px-5 text-sm font-bold text-white hover:bg-black transition-colors">
                        Buscar
                    </button>
                </form>

                <a href="{{ route('admin.users.create') }}"
                   class="h-11 flex items-center gap-2 rounded-xl bg-[#e11d48] px-6 text-sm font-bold text-white hover:bg-red-700 shadow-md shadow-red-100 transition-all active:scale-95">
                    <span class="text-lg leading-none">+</span> Nuevo Usuario
                </a>
            </div>
        </div>

        {{-- Flash messages --}}
        @if (session('success'))
            <div class="mx-6 mt-4 rounded-xl bg-green-50 border border-green-200 px-4 py-3 text-sm font-bold text-green-700">
                {{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div class="mx-6 mt-4 rounded-xl bg-red-50 border border-red-200 px-4 py-3 text-sm font-bold text-red-700">
                {{ session('error') }}
            </div>
        @endif

        {{-- Tabla --}}
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr>
                        <th class="px-6 py-4 bg-orange-50 text-[11px] font-black uppercase tracking-wider text-gray-700 border-b border-gray-100">Nombre</th>
                        <th class="px-6 py-4 bg-blue-50 text-[11px] font-black uppercase tracking-wider text-gray-700 border-b border-gray-100">Email</th>
                        <th class="px-6 py-4 bg-purple-50 text-[11px] font-black uppercase tracking-wider text-gray-700 border-b border-gray-100">Rol</th>
                        <th class="px-6 py-4 bg-yellow-50 text-[11px] font-black uppercase tracking-wider text-gray-700 border-b border-gray-100">Ubicaciones</th>
                        <th class="px-6 py-4 bg-green-50 text-[11px] font-black uppercase tracking-wider text-gray-700 border-b border-gray-100 text-center">Estado</th>
                        <th class="px-6 py-4 bg-gray-50 text-[11px] font-black uppercase tracking-wider text-gray-700 border-b border-gray-100 text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse ($users as $user)
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-6 py-4 text-sm font-bold text-gray-900">{{ $user->name }}</td>
                            <td class="px-6 py-4 text-sm text-gray-600">{{ $user->email }}</td>
                            <td class="px-6 py-4">
                                @php
                                    $roleColors = [
                                        'master'            => 'bg-red-100 text-red-700',
                                        'admin'             => 'bg-orange-100 text-orange-700',
                                        'warehouse_manager' => 'bg-blue-100 text-blue-700',
                                        'cashier'           => 'bg-green-100 text-green-700',
                                        'viewer'            => 'bg-gray-100 text-gray-600',
                                    ];
                                    $roleLabels = [
                                        'master'            => 'Master',
                                        'admin'             => 'Administrador',
                                        'warehouse_manager' => 'Almacenero',
                                        'cashier'           => 'Cajero',
                                        'viewer'            => 'Visor',
                                    ];
                                    $rv = $user->role->name->value;
                                    $rc = $roleColors[$rv] ?? 'bg-gray-100 text-gray-600';
                                    $rl = $roleLabels[$rv] ?? $rv;
                                @endphp
                                <span class="inline-block rounded-full px-3 py-1 text-xs font-black {{ $rc }}">
                                    {{ $rl }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">
                                @if ($user->locations->isEmpty())
                                    <span class="text-gray-400 italic text-xs">— Acceso global —</span>
                                @else
                                    <div class="flex flex-wrap gap-1">
                                        @foreach ($user->locations as $loc)
                                            <span class="inline-block rounded-full bg-blue-50 px-2 py-0.5 text-xs font-bold text-blue-700">{{ $loc->name }}</span>
                                        @endforeach
                                    </div>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center">
                                <form method="POST" action="{{ url('admin/users/' . $user->id . '/toggle') }}" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit"
                                        class="inline-flex items-center rounded-full px-3 py-1 text-xs font-black transition-all
                                        {{ $user->active ? 'bg-green-100 text-green-700 hover:bg-green-200' : 'bg-gray-100 text-gray-500 hover:bg-gray-200' }}">
                                        {{ $user->active ? 'Activo' : 'Inactivo' }}
                                    </button>
                                </form>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="{{ route('admin.users.edit', $user->id) }}"
                                       class="rounded-lg bg-blue-50 px-3 py-1.5 text-xs font-black text-blue-700 hover:bg-blue-100 transition-colors">
                                        Editar
                                    </a>
                                    @if ($user->id !== auth()->id())
                                        <form method="POST" action="{{ route('admin.users.destroy', $user->id) }}"
                                              onsubmit="return confirm('¿Eliminar al usuario {{ addslashes($user->name) }}? Esta acción es irreversible.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="rounded-lg bg-red-50 px-3 py-1.5 text-xs font-black text-red-600 hover:bg-red-100 transition-colors">
                                                Eliminar
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-sm text-gray-400 font-bold">
                                No se encontraron usuarios.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Paginación --}}
        @if ($users->hasPages())
            <div class="px-6 py-4 border-t border-gray-100">
                {{ $users->links() }}
            </div>
        @endif
    </div>
@endsection
