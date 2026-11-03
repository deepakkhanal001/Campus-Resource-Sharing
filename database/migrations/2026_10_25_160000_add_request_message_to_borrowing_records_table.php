<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('borrowing_records', function (Blueprint $table) {
            $table->text('request_message')->nullable()->after('status');
            $table->index(['resource_id', 'requester_id', 'status'], 'borrowing_records_active_request_index');
        });
    }

    public function down(): void
    {
        Schema::table('borrowing_records', function (Blueprint $table) {
            $table->dropIndex('borrowing_records_active_request_index');
            $table->dropColumn('request_message');
        });
    }
};
