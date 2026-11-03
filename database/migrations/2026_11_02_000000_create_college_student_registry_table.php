<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('college_student_registry', function (Blueprint $table) {
            $table->id();
            $table->string('registration_number', 32)->unique();
            $table->string('student_name', 150);
            $table->string('program', 100);
            $table->string('academic_year', 20);
            $table->boolean('is_active')->default(true)->index();
            $table->boolean('is_test_data')->default(false);
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('college_student_registry');
    }
};
