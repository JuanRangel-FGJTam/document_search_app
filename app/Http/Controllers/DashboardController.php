<?php

namespace App\Http\Controllers;

use App\Models\Misplacement;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    //
    const PENDING_STATUS = 1;
    const IN_PROGRESS_STATUS = 2;

    public function index()
    {
        $totalRequest = Misplacement::whereIn('lost_status_id', [
            self::PENDING_STATUS,
            self::IN_PROGRESS_STATUS,
        ])->count();
        return Inertia::render('Dashboard', [
            'totalRequest' => $totalRequest
        ]);
    }
}
