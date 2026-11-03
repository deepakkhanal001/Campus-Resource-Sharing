<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $connection = Schema::getConnection();
        $driver = $connection->getDriverName();

        if (! Schema::hasColumn("users", "college_student_id")) {
            if ($driver === "mysql" || $driver === "mariadb") {
                DB::statement("ALTER TABLE users ADD COLUMN college_student_id VARCHAR(191) NULL AFTER email");
                try { DB::statement("ALTER TABLE users ADD UNIQUE INDEX users_college_student_id_unique (college_student_id)"); } catch (\Throwable $e) {}
            } elseif ($driver === "pgsql") {
                DB::statement("ALTER TABLE users ADD COLUMN college_student_id VARCHAR(191)");
                try { DB::statement("CREATE UNIQUE INDEX users_college_student_id_unique ON users (college_student_id)"); } catch (\Throwable $e) {}
            } else {
                DB::statement("ALTER TABLE users ADD COLUMN college_student_id VARCHAR(191) NULL");
                try { DB::statement("CREATE UNIQUE INDEX users_college_student_id_unique ON users (college_student_id)"); } catch (\Throwable $e) {}
            }
        }

        if (! Schema::hasColumn("users", "program")) {
            if ($driver === "mysql" || $driver === "mariadb") {
                DB::statement("ALTER TABLE users ADD COLUMN program VARCHAR(100) NULL");
            } else {
                DB::statement("ALTER TABLE users ADD COLUMN program VARCHAR(100) NULL");
            }
        }

        if (! Schema::hasColumn("users", "verification_status")) {
            if ($driver === "mysql" || $driver === "mariadb") {
                DB::statement("ALTER TABLE users ADD COLUMN verification_status ENUM('pending','verified','rejected') NOT NULL DEFAULT 'pending'");
            } elseif ($driver === "pgsql") {
                try { DB::statement("CREATE TYPE verification_status_enum AS ENUM ('pending','verified','rejected')"); } catch (\Throwable $e) {}
                DB::statement("ALTER TABLE users ADD COLUMN verification_status verification_status_enum NOT NULL DEFAULT 'pending'");
            } else {
                DB::statement("ALTER TABLE users ADD COLUMN verification_status VARCHAR(20) NOT NULL DEFAULT 'pending'");
            }
        }

        if (! Schema::hasColumn("users", "verified_at")) {
            DB::statement("ALTER TABLE users ADD COLUMN verified_at TIMESTAMP NULL");
        }

        if (! Schema::hasColumn("users", "verified_by")) {
            if ($driver === "mysql" || $driver === "mariadb") {
                DB::statement("ALTER TABLE users ADD COLUMN verified_by BIGINT UNSIGNED NULL");
                try { DB::statement("ALTER TABLE users ADD CONSTRAINT users_verified_by_foreign FOREIGN KEY (verified_by) REFERENCES users(id) ON DELETE SET NULL"); } catch (\Throwable $e) {}
            } else {
                DB::statement("ALTER TABLE users ADD COLUMN verified_by INTEGER NULL");
                try { DB::statement("ALTER TABLE users ADD CONSTRAINT users_verified_by_foreign FOREIGN KEY (verified_by) REFERENCES users(id) ON DELETE SET NULL"); } catch (\Throwable $e) {}
            }
        }

        if (! Schema::hasColumn("users", "rejection_reason")) {
            DB::statement("ALTER TABLE users ADD COLUMN rejection_reason TEXT NULL");
        }

        if (! Schema::hasColumn("users", "rejected_at")) {
            DB::statement("ALTER TABLE users ADD COLUMN rejected_at TIMESTAMP NULL");
        }

        // Ensure existing rows have valid verification_status
        try {
            DB::statement("UPDATE users SET verification_status = 'verified' WHERE verification_status IS NULL");
        } catch (\Throwable $e) {}
        try {
            if ($driver === "sqlite") {
                // sqlite might need to ensure not null
            }
        } catch (\Throwable $e) {}
    }

    public function down(): void
    {
        $connection = Schema::getConnection();
        $driver = $connection->getDriverName();
        try {
            if ($driver === "mysql" || $driver === "mariadb") {
                DB::statement("ALTER TABLE users DROP FOREIGN KEY users_verified_by_foreign");
            }
        } catch (\Throwable $e) {}

        $columns = ["college_student_id", "program", "verification_status", "verified_at", "verified_by", "rejection_reason", "rejected_at"];
        foreach ($columns as $column) {
            if (Schema::hasColumn("users", $column)) {
                try {
                    if ($column === "college_student_id") {
                        try { DB::statement("DROP INDEX users_college_student_id_unique ON users"); } catch (\Throwable $e) {}
                    }
                    DB::statement("ALTER TABLE users DROP COLUMN {$column}");
                } catch (\Throwable $e) {}
            }
        }
    }
};
