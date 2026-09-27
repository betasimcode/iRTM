<?php

// app/View/Components/UI/Button.php

namespace App\View\Components\UI;

use Illuminate\View\Component;

class Button extends Component
{
    public $href;
    public $variant;
    public $size;

    public function __construct($href = null, $variant = 'primary', $size = 'md')
    {
        $this->href = $href;
        $this->variant = $variant;
        $this->size = $size;
    }

    public function render()
    {
        return view('components.ui.button');
    }
}
