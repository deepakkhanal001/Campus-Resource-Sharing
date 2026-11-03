<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

class PublicCatalog
{
    public const RESOURCE_SELECT = <<<'SQL'
        SELECT
            r.id,
            r.title,
            r.description,
            r.type,
            r.owner_id,
            r.category_id,
            r.availability_status,
            r.created_at,
            c.name AS category_name,
            u.name AS owner_name,
            u.role AS owner_role
        SQL;

    public const RESOURCE_FROM = <<<'SQL'
        FROM resources r
        INNER JOIN categories c ON c.id = r.category_id AND c.deleted_at IS NULL
        INNER JOIN users u ON u.id = r.owner_id
            AND u.status = 'active'
        SQL;

    public static function visibleResourceSql(): string
    {
        return self::RESOURCE_SELECT.' '.self::RESOURCE_FROM.' WHERE r.deleted_at IS NULL';
    }

    public static function findVisibleResource(int $resourceId): ?object
    {
        return DB::selectOne(
            self::visibleResourceSql().' AND r.id = ? LIMIT 1',
            [$resourceId]
        );
    }

    public static function findUser(int $userId): ?object
    {
        return DB::selectOne(
            'SELECT id, name, email, role, status, college_student_id
             FROM users
             WHERE id = ?
             LIMIT 1',
            [$userId]
        );
    }

    public static function unreadNotificationCount(int $userId): int
    {
        $row = DB::selectOne(
            'SELECT COUNT(*) AS total FROM notifications WHERE user_id = ? AND is_read = 0 AND deleted_at IS NULL',
            [$userId]
        );

        return (int) ($row->total ?? 0);
    }

    public static function categoriesWithCounts(): array
    {
        return DB::select(<<<'SQL'
            SELECT
                c.id,
                c.name,
                c.description,
                COUNT(r.id) AS resources_count
            FROM categories c
            LEFT JOIN resources r
                ON r.category_id = c.id
                AND r.deleted_at IS NULL
                AND r.availability_status = 'available'
                AND EXISTS (
                    SELECT 1
                    FROM users u
                    WHERE u.id = r.owner_id
                      AND u.status = 'active'
                )
            WHERE c.deleted_at IS NULL
            GROUP BY c.id, c.name, c.description
            ORDER BY c.name ASC
            SQL);
    }

    public static function availableFiles(int $resourceId): array
    {
        return DB::select(
            'SELECT id, file_name, file_type, size, download_count, created_at
             FROM digital_files
             WHERE resource_id = ? AND deleted_at IS NULL
             ORDER BY created_at ASC, id ASC',
            [$resourceId]
        );
    }
}
