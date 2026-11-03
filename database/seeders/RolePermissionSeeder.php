<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $actions = [
            'create', 'index', 'edit', 'destroy', 'report', 'show', 'print',
        ];

        $models = [
            'permission', 'role', 'user',
            'category', 'program', 'resource', 'digital-file',
            'borrowing-record', 'notification',
        ];

        foreach ($actions as $action) {
            foreach ($models as $model) {
                Permission::firstOrCreate([
                    'name' => $action.'-'.$model,
                    'guard_name' => 'web',
                ]);
            }
        }

        $adminRole = Role::firstOrCreate([
            'name' => 'admin',
            'guard_name' => 'web',
        ]);

        Role::firstOrCreate([
            'name' => 'student',
            'guard_name' => 'web',
        ]);

        $adminRole->syncPermissions(
            Permission::where('guard_name', 'web')->get()
        );

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
