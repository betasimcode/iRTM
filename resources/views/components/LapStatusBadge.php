<?php
namespace App\View\Components\layout;

use Illuminate\View\Component;

class LapStatusBadge extends Component
{
    public $lap;

    public function __construct($lap)
    {
        $this->lap = $lap;
    }

    public function render()
    {
        return view('components.lap-status-badge');
    }
}