<?php

namespace App\Http\Controllers;

use App\Support\PublicCatalog;
use Illuminate\View\View;

class PublicCategoryController extends Controller
{
    public function index(): View
    {
        return view('public.categories.index', [
            'categories' => PublicCatalog::categoriesWithCounts(),
        ]);
    }
}
