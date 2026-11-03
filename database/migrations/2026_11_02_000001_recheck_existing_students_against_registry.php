<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Existing student accounts were verified automatically by an earlier
     * migration. Re-check them against the new registry so that accounts
     * without an active registry record must pass through admin verification.
     *
     * This migration intentionally runs before the dummy registry seeder; all
     * existing students will be pending until an administrator reviews them
     * or the official college list is imported.
     */
    public function up(): void
    {
        DB::update(
            "UPDATE users
             SET verification_status = 'pending',
                 verified_at = NULL,
                 verified_by = NULL,
                 updated_at = ?
             WHERE LOWER(role) = 'student'
               AND verification_status = 'verified'
               AND (
                    college_student_id IS NULL
                    OR NOT EXISTS (
                        SELECT 1
                        FROM college_student_registry registry
                        WHERE registry.registration_number = users.college_student_id
                          AND registry.is_active = 1
                    )
               )",
            [now()->toDateTimeString()]
        );
    }

    public function down(): void
    {
        // Verification state changes are intentionally not reversed.
    }
};
