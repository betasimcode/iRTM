<?php

namespace App\Http\Controllers;

use App\Models\IrSession;

use App\Services\SessionRecoveryService;

class SessionRecoveryController extends Controller
{
    public function rebuild(

        IrSession $session,

        SessionRecoveryService $recoveryService
    ) {

        $result = $recoveryService->rebuild(
            $session
        );

        return back()->with(

            'success',

            $result
        );
    }
}