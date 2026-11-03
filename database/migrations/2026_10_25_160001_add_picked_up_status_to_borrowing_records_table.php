<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement(
                "ALTER TABLE borrowing_records
                 MODIFY status ENUM('pending', 'approved', 'picked_up', 'returned', 'rejected', 'cancelled')
                 NOT NULL DEFAULT 'pending'"
            );
        }
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::table('borrowing_records')
                ->where('status', 'picked_up')
                ->update(['status' => 'approved']);

            DB::statement(
                "ALTER TABLE borrowing_records
                 MODIFY status ENUM('pending', 'approved', 'rejected', 'returned', 'cancelled')
                 NOT NULL DEFAULT 'pending'"
            );
        }
    }
};
