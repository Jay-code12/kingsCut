<?php

namespace App\Controllers;

use App\Core\View;
use App\Models\WorkItem;

class WorkController
{
    public function index(): void
    {
        View::render('work', [
            'title' => 'Our Work',
            'items' => WorkItem::allPublic(),
        ]);
    }
}
