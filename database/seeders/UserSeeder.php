<?php

namespace Database\Seeders;

use App\Enums\RoleName;
use App\Models\Location;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $masterRole = Role::where('name', RoleName::Master->value)->firstOrFail();

        $user = User::firstOrCreate(
            ['email' => 'master@psf.local'],
            [
                'name'     => 'Master PSF',
                'password' => Hash::make('secret_change_me'),
                'role_id'  => $masterRole->id,
                'active'   => true,
            ]
        );

        // Assign all locations via user_locations (sync avoids duplicates)
        $allLocationIds = Location::pluck('id')->all();
        $user->locations()->sync($allLocationIds);
    }
}
