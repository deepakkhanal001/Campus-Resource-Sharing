<?php

namespace Database\Seeders;

use App\Models\Program;
use Illuminate\Database\Seeder;

class ProgramSeeder extends Seeder
{
    public function run(): void
    {
        $programs = [
            ['code' => 'BCA', 'name' => 'Bachelor of Computer Application'],
            ['code' => 'BBA', 'name' => 'Bachelor of Business Administration'],
            ['code' => 'BSc CSIT', 'name' => 'Bachelor of Science in Computer Science and Information Technology'],
            ['code' => 'BA', 'name' => 'Bachelor of Arts'],
        ];

        foreach ($programs as $program) {
            Program::updateOrCreate(
                ['code' => $program['code']],
                [
                    'name' => $program['name'],
                    'is_active' => true,
                ]
            );
        }
    }
}
