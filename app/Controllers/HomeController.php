<?php

namespace App\Controllers;

use App\Core\View;
use App\Models\Plan;
use App\Models\WorkItem;

class HomeController
{
    public function index(): void
    {
        $plans = Plan::allWithPrices();
        View::render('home', [
            'title' => 'Home',
            'plans' => $plans,
            'workItems' => WorkItem::allPublic(4),
        ]);
    }
}
