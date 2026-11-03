<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'verification_status')) {
            return;
        }

        $now = now()->toDateTimeString();

        DB::table('users')
            ->where(function ($query) {
                $query->whereRaw('LOWER(role) = ?', ['student'])
                    ->orWhereNotNull('college_student_id');
            })
            ->where('verification_status', '!=', 'verified')
            ->update([
                'verification_status' => 'verified',
                'status' => 'active',
                'verified_at' => $now,
                'verified_by' => null,
                'rejection_reason' => null,
                'rejected_at' => null,
                'updated_at' => $now,
            ]);
    }

    public function down(): void
    {
        //
    }
};
