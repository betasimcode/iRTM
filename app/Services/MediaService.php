<?php

namespace App\Services;

class MediaService
{
    /**
     * URL base del renderer local de iRacing.
     */
    private const IRACING_RENDER = 'http://localhost:32034';

    /**
     * Devuelve la URL del casco de un piloto.
     */
    public function helmetUrl(int $memberId): string
    {
        $path = env('IRACING_DOCUMENTS')
            . DIRECTORY_SEPARATOR
            . 'paint'
            . DIRECTORY_SEPARATOR
            . "helmet_{$memberId}.tga";

        // Si el casco no existe, devolver el casco genérico.
        if (!file_exists($path)) {
            return self::IRACING_RENDER . '/pk_helmet.png';
        }

        return self::IRACING_RENDER
            . '/pk_helmet.png?size=2&hlmtCustPaint='
            . rawurlencode($path);
    }
}
