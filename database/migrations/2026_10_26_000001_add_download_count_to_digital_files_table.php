<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('digital_files', function (Blueprint $table) {
            $table->unsignedBigInteger('download_count')->default(0)->after('size');
        });
    }

    public function down(): void
    {
        Schema::table('digital_files', function (Blueprint $table) {
            $table->dropColumn('download_count');
        });
    }
};
