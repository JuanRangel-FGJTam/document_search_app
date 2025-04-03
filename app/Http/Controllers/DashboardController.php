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
        $today = new \Carbon\Carbon();
        $totalRequest = Misplacement::whereDate('registration_date', $today->toDateString())->count();

        return Inertia::render('Dashboard', [
            'totalRequest' => $totalRequest
        ]);
    }
}
