<?php

namespace App\Http\Controllers;

use App\Support\PublicCatalog;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PublicHomeController extends Controller
{
    public function index(): View
    {
        $stats = DB::selectOne(
            'SELECT
                COUNT(*) AS available_count,
                SUM(CASE WHEN r.type = \'physical\' THEN 1 ELSE 0 END) AS physical_count,
                SUM(CASE WHEN r.type = \'digital\' THEN 1 ELSE 0 END) AS digital_count '
            .PublicCatalog::RESOURCE_FROM.
            " WHERE r.deleted_at IS NULL AND r.availability_status = 'available'"
        );

        $resources = DB::select(
            PublicCatalog::RESOURCE_SELECT.' '.PublicCatalog::RESOURCE_FROM.
            " WHERE r.deleted_at IS NULL AND r.availability_status = 'available'
              ORDER BY r.created_at DESC, r.id DESC
              LIMIT 6"
        );

        return view('public.home', [
            'categories' => PublicCatalog::categoriesWithCounts(),
            'resources' => $resources,
            'stats' => [
                'available' => (int) ($stats->available_count ?? 0),
                'physical' => (int) ($stats->physical_count ?? 0),
                'digital' => (int) ($stats->digital_count ?? 0),
            ],
        ]);
    }
}
