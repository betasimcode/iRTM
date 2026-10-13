<?php

namespace App\Services\Ibt;

use App\Models\Stint;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class IbtRepairService
{
    public function repair(Stint $stint): array
    {
        $ibt = $stint->files()
            ->where('type', 'ibt')
            ->first();

        if (!$ibt) {
            throw new RuntimeException(
                'No existe un archivo IBT registrado para este stint.'
            );
        }

        $disk = Storage::disk('local');

        if (!$disk->exists($ibt->filepath)) {
            throw new RuntimeException(
                'El archivo IBT no existe en el almacenamiento.'
            );
        }

        $filepath = $disk->path($ibt->filepath);

        $response = Http::timeout(180)
            ->post('http://127.0.0.1:32000/process-ibt', [
                'filepath' => $filepath,
            ]);

        if (!$response->successful()) {
            throw new RuntimeException(
                'El reprocesamiento IBT ha fallado. HTTP '
                . $response->status()
            );
        }

        return [
            'status' => 'ok',
            'stint_id' => $stint->id,
            'response' => $response->json(),
        ];
    }
}
