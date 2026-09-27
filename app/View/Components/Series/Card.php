<?php

namespace App\View\Components\Series;

use Illuminate\View\Component;

class Card extends Component
{
    public $serie;

    public function __construct($serie)
    {
        $this->serie = $serie;
    }

    public function render()
    {
        return view('components.series.card');
    }
}
