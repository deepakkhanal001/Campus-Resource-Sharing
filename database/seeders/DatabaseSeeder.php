<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
            ProgramSeeder::class,
            CollegeStudentRegistrySeeder::class,
        ]);

        $admin = User::updateOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'name' => 'Administrator',
                'password' => Hash::make('admin@123'),
                'role' => 'admin',
                'status' => 'active',
            ]
        );

        $admin->forceFill(['email_verified_at' => now()])->save();
        $admin->syncRoles(['admin']);

        $this->command->info('Admin credentials: admin@gmail.com / admin@123');
    }
}
