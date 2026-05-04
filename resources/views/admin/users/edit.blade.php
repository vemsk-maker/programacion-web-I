@extends('layouts.app')

@section('content')
    <div class="mb-6">
        <x-common.page-breadcrumb pageTitle="Editar Usuario" />
    </div>

    @php
        $rolesNeedLocation = ['warehouse_manager', 'cashier', 'viewer'];
        $currentRoleValue  = $user->role->name->value;
        $initNeedsLocation = in_array($currentRoleValue, $rolesNeedLocation) ? 'true' : 'false';
    @endphp

    <div class="max-w-2xl"
         x-data="{
             roleNeedsLocation: {{ $initNeedsLocation }},
             rolesNeedLocation: ['warehouse_manager', 'cashier', 'viewer'],
             checkRole(roleValue) {
                 this.roleNeedsLocation = this.rolesNeedLocation.includes(roleValue);
             }
         }">
        <div class="bg-white rounded-3xl border border-gray-200 shadow-sm overflow-hidden">

            <div class="p-6 border-b border-gray-100">
                <h3 class="text-lg font-black text-gray-900 uppercase tracking-tight">Editar Usuario</h3>
                <p class="text-xs text-gray-500 font-bold">Modifique los datos del usuario: <span class="text-gray-700">{{ $user->name }}</span></p>
            </div>

            <form method="POST" action="{{ route('admin.users.update', $user->id) }}" class="p-6 space-y-5">
                @csrf
                @method('PUT')

                {{-- Nombre --}}
                <div>
                    <label class="mb-1.5 block text-sm font-black text-gray-700 uppercase">
                        Nombre <span class="text-red-600">*</span>
                    </label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}"
                        placeholder="Nombre completo"
                        class="h-11 w-full rounded-xl border {{ $errors->has('name') ? 'border-red-500' : 'border-gray-300' }} bg-white px-4 py-2.5 text-sm text-gray-800 font-bold focus:border-red-500 focus:ring-0 outline-none"
                        required />
                    @error('name')
                        <p class="mt-1 text-xs font-black text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Email --}}
                <div>
                    <label class="mb-1.5 block text-sm font-black text-gray-700 uppercase">
                        Correo electrónico <span class="text-red-600">*</span>
                    </label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}"
                        placeholder="usuario@ejemplo.com"
                        class="h-11 w-full rounded-xl border {{ $errors->has('email') ? 'border-red-500' : 'border-gray-300' }} bg-white px-4 py-2.5 text-sm text-gray-800 font-bold focus:border-red-500 focus:ring-0 outline-none"
                        required />
                    @error('email')
                        <p class="mt-1 text-xs font-black text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Contraseña (opcional al editar) --}}
                <div class="rounded-xl border border-dashed border-gray-300 bg-gray-50 p-4 space-y-3">
                    <p class="text-xs font-black text-gray-500 uppercase">Cambiar contraseña (dejar en blanco para mantener la actual)</p>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="mb-1.5 block text-sm font-black text-gray-700 uppercase">Nueva contraseña</label>
                            <input type="password" name="password"
                                placeholder="Mínimo 8 caracteres"
                                class="h-11 w-full rounded-xl border {{ $errors->has('password') ? 'border-red-500' : 'border-gray-300' }} bg-white px-4 py-2.5 text-sm text-gray-800 font-bold focus:border-red-500 focus:ring-0 outline-none" />
                            @error('password')
                                <p class="mt-1 text-xs font-black text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="mb-1.5 block text-sm font-black text-gray-700 uppercase">Confirmar contraseña</label>
                            <input type="password" name="password_confirmation"
                                placeholder="Repita la contraseña"
                                class="h-11 w-full rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 font-bold focus:border-red-500 focus:ring-0 outline-none" />
                        </div>
                    </div>
                </div>

                {{-- Rol --}}
                <div>
                    <label class="mb-1.5 block text-sm font-black text-gray-700 uppercase">
                        Rol <span class="text-red-600">*</span>
                    </label>
                    <select name="role_id"
                        @change="checkRole($event.target.selectedOptions[0]?.dataset?.rolename ?? '')"
                        class="h-11 w-full rounded-xl border {{ $errors->has('role_id') ? 'border-red-500' : 'border-gray-300' }} bg-white px-4 py-2.5 text-sm text-gray-800 font-bold focus:border-red-500 focus:ring-0 outline-none"
                        required>
                        @foreach ($roles as $role)
                            @php
                                $roleLabels = [
                                    'master'            => 'Master (acceso total)',
                                    'admin'             => 'Administrador',
                                    'warehouse_manager' => 'Almacenero',
                                    'cashier'           => 'Cajero',
                                    'viewer'            => 'Visor (solo lectura)',
                                ];
                                $rl = $roleLabels[$role->name->value] ?? $role->name->value;
                            @endphp
                            <option value="{{ $role->id }}"
                                data-rolename="{{ $role->name->value }}"
                                {{ old('role_id', $user->role_id) == $role->id ? 'selected' : '' }}>
                                {{ $rl }}
                            </option>
                        @endforeach
                    </select>
                    @error('role_id')
                        <p class="mt-1 text-xs font-black text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Ubicaciones (condicional según rol) --}}
                <div x-show="roleNeedsLocation" x-transition>
                    <label class="mb-1.5 block text-sm font-black text-gray-700 uppercase">
                        Ubicaciones asignadas <span class="text-red-600">*</span>
                    </label>
                    <p class="mb-2 text-xs text-gray-500">Este rol requiere asignación a al menos una sucursal o almacén.</p>
                    <div class="rounded-xl border border-gray-300 bg-gray-50 p-3 max-h-48 overflow-y-auto space-y-2">
                        @foreach ($locations as $loc)
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox"
                                    name="location_ids[]"
                                    value="{{ $loc->id }}"
                                    {{ in_array($loc->id, old('location_ids', $assignedLocationIds)) ? 'checked' : '' }}
                                    class="rounded border-gray-300 text-red-600 focus:ring-red-500" />
                                <span class="text-sm font-bold text-gray-700">{{ $loc->name }}</span>
                                <span class="text-xs text-gray-400 capitalize">({{ $loc->type->value }})</span>
                            </label>
                        @endforeach
                    </div>
                    @error('location_ids')
                        <p class="mt-1 text-xs font-black text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Estado activo --}}
                <div x-data="{ active: {{ old('active', $user->active) ? 'true' : 'false' }} }">
                    <label class="flex cursor-pointer items-center gap-3 text-sm font-black text-gray-700 select-none uppercase">
                        <div class="relative">
                            <input type="hidden" name="active" :value="active ? '1' : '0'" />
                            <input type="checkbox" class="sr-only" @change="active = !active" :checked="active" />
                            <div class="block h-6 w-11 rounded-full transition-colors duration-300"
                                :class="active ? 'bg-red-500' : 'bg-gray-200'"></div>
                            <div :class="active ? 'translate-x-full' : 'translate-x-0'"
                                class="absolute top-0.5 left-0.5 h-5 w-5 rounded-full bg-white shadow-sm duration-300 ease-linear"></div>
                        </div>
                        <span x-text="active ? 'Usuario Activo' : 'Usuario Inactivo'"></span>
                    </label>
                </div>

                {{-- Botones --}}
                <div class="flex items-center gap-3 pt-4 border-t border-gray-100">
                    <button type="submit"
                        class="rounded-xl bg-[#e11d48] px-8 py-2.5 text-sm font-black text-white uppercase hover:bg-red-700 shadow-md shadow-red-100 transition-all active:scale-95">
                        Actualizar
                    </button>
                    <a href="{{ route('admin.users.index') }}"
                       class="rounded-xl bg-gray-100 px-6 py-2.5 text-sm font-black text-gray-700 uppercase hover:bg-gray-200 transition-all">
                        Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>
@endsection
