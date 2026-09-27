<?php

namespace App\Http\Controllers;

use App\Models\Driver;

class AdminController extends Controller
{
    public function loggerStatus()
    {
        $drivers = Driver::with('user')
            ->orderBy('last_logger_ping', 'desc')
            ->get();

        return view('admin.logger-status', compact('drivers'));
    }
}
