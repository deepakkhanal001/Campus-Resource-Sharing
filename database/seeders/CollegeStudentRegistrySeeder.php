<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CollegeStudentRegistrySeeder extends Seeder
{
    /**
     * TEST DATA ONLY.
     *
     * These are not real Gomendra Multiple College students and the
     * registration numbers are invented. Replace this seeder with an import
     * of the official college-provided student list before relying on the
     * registry for real identity verification.
     *
     * Every record is flagged with is_test_data = 1 so it can be identified
     * and removed/replaced safely.
     */
    private const TEST_RECORDS = [
        [
            'registration_number' => '12-3-4-56789-1234',
            'student_name' => 'Bhim Khanal',
            'program' => 'BCA',
            'academic_year' => '2081/2082',
            'is_active' => 1,
        ],
        [
            'registration_number' => '12-3-4-56789-5678',
            'student_name' => 'Dummy Student Beta',
            'program' => 'BBA',
            'academic_year' => '2081/2082',
            'is_active' => 1,
        ],
        [
            'registration_number' => '12-3-4-56789-9012',
            'student_name' => 'Dummy Student Gamma',
            'program' => 'BSc CSIT',
            'academic_year' => '2080/2081',
            'is_active' => 1,
        ],
        [
            'registration_number' => '45-6-7-12345-6789',
            'student_name' => 'Dummy Student Delta',
            'program' => 'BBS',
            'academic_year' => '2081/2082',
            'is_active' => 1,
        ],
        [
            'registration_number' => '99-9-9-99999-9999',
            'student_name' => 'Dummy Inactive Student',
            'program' => 'BA',
            'academic_year' => '2080/2081',
            'is_active' => 0,
        ],
    ];

    public function run(): void
    {
        if (app()->environment('production')) {
            $this->command?->warn(
                'Skipped CollegeStudentRegistrySeeder in production. Import the official college student list instead.'
            );

            return;
        }

        $pdo = DB::connection()->getPdo();

        $find = $pdo->prepare(
            'SELECT id FROM college_student_registry WHERE registration_number = ? LIMIT 1'
        );

        $insert = $pdo->prepare(
            'INSERT INTO college_student_registry
                (registration_number, student_name, program, academic_year, is_active, is_test_data, created_at)
             VALUES
                (?, ?, ?, ?, ?, 1, ?)'
        );

        $update = $pdo->prepare(
            'UPDATE college_student_registry
             SET student_name = ?,
                 program = ?,
                 academic_year = ?,
                 is_active = ?,
                 is_test_data = 1
             WHERE registration_number = ?'
        );

        $now = now()->toDateTimeString();

        foreach (self::TEST_RECORDS as $record) {
            $find->execute([$record['registration_number']]);

            if ($find->fetch()) {
                $update->execute([
                    $record['student_name'],
                    $record['program'],
                    $record['academic_year'],
                    $record['is_active'],
                    $record['registration_number'],
                ]);

                continue;
            }

            $insert->execute([
                $record['registration_number'],
                $record['student_name'],
                $record['program'],
                $record['academic_year'],
                $record['is_active'],
                $now,
            ]);
        }

        $this->command?->info('Inserted/updated dummy college registry records (is_test_data = 1).');
    }
}
