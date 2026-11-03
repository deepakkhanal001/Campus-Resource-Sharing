<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        foreach (['categories', 'resources', 'digital_files', 'borrowing_records', 'notifications'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->timestamp('updated_at')->nullable();
                $table->softDeletes();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach (['categories', 'resources', 'digital_files', 'borrowing_records', 'notifications'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropSoftDeletes();
                $table->dropColumn('updated_at');
            });
        }
    }
};
