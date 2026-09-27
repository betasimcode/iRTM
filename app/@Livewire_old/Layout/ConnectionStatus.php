<?php

namespace App\Livewire\Layout;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class ConnectionStatus extends Component
{
    public function render()
    {
        $user = Auth::user();

        $status = 'Offline';

        if ($user) {

            if ($user->last_logger_ping &&
                now()->diffInSeconds($user->last_logger_ping) > 20) {

                $status = 'Offline';

            } else {

                $status = $user->current_status ?? 'Offline';

            }

        }

        return view('livewire.layout.connection-status', [
            'status' => $status
        ]);
    }
}
