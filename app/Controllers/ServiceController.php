<?php

namespace App\Controllers;

use App\Core\View;
use App\Models\ServiceCatalog;

class ServiceController
{
    public function index(): void
    {
        $categories = ServiceCatalog::allGrouped();
        View::render('services', [
            'title' => 'Services',
            'categories' => $categories,
        ]);
    }
}
