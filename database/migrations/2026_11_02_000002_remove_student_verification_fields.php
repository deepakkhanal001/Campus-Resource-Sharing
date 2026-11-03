<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            try {
                DB::statement('ALTER TABLE users DROP FOREIGN KEY users_verified_by_foreign');
            } catch (Throwable $e) {
                // The constraint may already be absent or named differently.
            }
        }

        foreach (['verified_by', 'verified_at', 'rejection_reason', 'rejected_at', 'verification_status'] as $column) {
            if (Schema::hasColumn('users', $column)) {
                Schema::table('users', function (Blueprint $table) use ($column): void {
                    $table->dropColumn($column);
                });
            }
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumn('users', 'verification_status')) {
            DB::statement("ALTER TABLE users ADD COLUMN verification_status VARCHAR(20) NOT NULL DEFAULT 'verified'");
        }

        if (! Schema::hasColumn('users', 'verified_at')) {
            DB::statement('ALTER TABLE users ADD COLUMN verified_at TIMESTAMP NULL');
        }

        if (! Schema::hasColumn('users', 'verified_by')) {
            DB::statement('ALTER TABLE users ADD COLUMN verified_by INTEGER NULL');
        }

        if (! Schema::hasColumn('users', 'rejection_reason')) {
            DB::statement('ALTER TABLE users ADD COLUMN rejection_reason TEXT NULL');
        }

        if (! Schema::hasColumn('users', 'rejected_at')) {
            DB::statement('ALTER TABLE users ADD COLUMN rejected_at TIMESTAMP NULL');
        }
    }
};
