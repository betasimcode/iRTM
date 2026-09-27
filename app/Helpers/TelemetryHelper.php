<?php

namespace App\Helpers;

class TelemetryHelper
{
    public static function tempColor($t)
    {
        if ($t > 100) return 'bg-red-500 text-white';
        if ($t > 90) return 'bg-yellow-400 text-black';
        if ($t < 70) return 'bg-blue-400 text-white';
        return 'bg-green-500 text-black';
    }
}