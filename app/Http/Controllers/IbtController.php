<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use App\Jobs\ProcessIbtSession;

class IbtController extends Controller
{
    public function upload(Request $request)
    {
        $request->validate([
            'ibt' => 'required|file',
            'iracing_subsession_id' => 'nullable|string'
        ]);

        try {
            $path = $request->file('ibt')->store('ibt');

            // 🔥 dispatch automático
            ProcessIbtSession::dispatch($path);

            return response()->json([
                'status' => 'queued',
                'path' => $path
            ]);

        } catch (\Exception $e) {
            Log::error("IBT upload error: ".$e->getMessage());

            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }
}