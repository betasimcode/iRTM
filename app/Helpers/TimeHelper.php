<?php

if (!function_exists('lapTime')) {

    function lapTime($seconds)
    {
        if (!$seconds || $seconds <= 0) {
            return '-';
        }

        $seconds = (float) $seconds;

        $minutes = floor($seconds / 60);
        $remaining = $seconds - ($minutes * 60);

        return sprintf('%d:%06.3f', $minutes, $remaining);
    }

    function sectorTime($seconds)
    {
        if (!$seconds || $seconds <= 0) {
            return '-';
        }

        return number_format((float)$seconds, 3);
    }

}
