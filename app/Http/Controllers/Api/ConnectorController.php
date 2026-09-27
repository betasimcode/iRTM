<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ConnectorController extends Controller
{
    public function driver(Request $request)
    {
        return response()->json([

            'success' => true,

            'member_id' => $request->memberId,

            'message' => 'Driver context received'

        ]);
    }
}
