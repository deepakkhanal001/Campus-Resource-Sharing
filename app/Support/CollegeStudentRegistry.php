<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

class CollegeStudentRegistry
{
    /**
     * Required college registration number format: XX-X-X-XXXXX-XXXX
     * Example (format only): 12-3-4-56789-1234
     */
    public const REGISTRATION_NUMBER_PATTERN = '/^\d{2}-\d-\d-\d{5}-\d{4}$/';

    public static function normalizeRegistrationNumber(string $value): string
    {
        $value = str_replace(["\u{2013}", "\u{2014}", "\u{2212}"], '-', trim($value));

        return $value;
    }

    public static function isValidRegistrationNumber(string $value): bool
    {
        return preg_match(self::REGISTRATION_NUMBER_PATTERN, $value) === 1;
    }

    public static function normalizeStudentName(string $value): string
    {
        $value = str_replace("\u{00A0}", ' ', $value);
        $value = preg_replace('/\s+/u', ' ', trim($value)) ?? trim($value);

        return mb_strtolower($value, 'UTF-8');
    }

    public static function namesMatch(string $submittedName, string $registryName): bool
    {
        return self::normalizeStudentName($submittedName) === self::normalizeStudentName($registryName);
    }

    public static function findByRegistrationNumber(string $registrationNumber): ?object
    {
        $row = DB::selectOne(
            'SELECT id, registration_number, student_name, program, academic_year, is_active, is_test_data
             FROM college_student_registry
             WHERE registration_number = ?
             LIMIT 1',
            [$registrationNumber]
        );

        return $row ?: null;
    }

    public static function registrationNumberUsedByUser(string $registrationNumber, ?int $exceptUserId = null): bool
    {
        if ($exceptUserId !== null) {
            return (bool) DB::selectOne(
                'SELECT id FROM users
                 WHERE college_student_id = ? AND id <> ?
                 LIMIT 1',
                [$registrationNumber, $exceptUserId]
            );
        }

        return (bool) DB::selectOne(
            'SELECT id FROM users
             WHERE college_student_id = ?
             LIMIT 1',
            [$registrationNumber]
        );
    }

    public static function emailUsedByUser(string $email): bool
    {
        return (bool) DB::selectOne(
            'SELECT id FROM users
             WHERE LOWER(email) = ?
             LIMIT 1',
            [mb_strtolower(trim($email), 'UTF-8')]
        );
    }

    public static function account(int $userId): ?object
    {
        $row = DB::selectOne(
            'SELECT id, name, email, role, status, college_student_id
             FROM users
             WHERE id = ?
             LIMIT 1',
            [$userId]
        );

        return $row ?: null;
    }

    public static function isAdmin(?object $account): bool
    {
        return $account !== null
            && strtolower((string) ($account->role ?? '')) === 'admin';
    }

    public static function isActiveStudent(?object $account): bool
    {
        return $account !== null
            && strtolower((string) ($account->role ?? '')) === 'student'
            && strtolower((string) ($account->status ?? '')) === 'active';
    }
}
