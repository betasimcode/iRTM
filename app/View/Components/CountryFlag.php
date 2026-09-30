<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\Contracts\View\View;

class CountryFlag extends Component
{
    public ?string $flagCode;

    public string $label;

    public function __construct(?string $code)
    {
        $code = strtoupper(trim((string) $code));

        /*
         * Códigos especiales / no ISO utilizados
         * por iRacing o que queremos representar
         * con una bandera regional.
         */
        $specialFlags = [

            // Reino Unido
            'ENG' => [
                'flag' => 'gb-eng',
                'label' => 'England',
            ],

            'SCO' => [
                'flag' => 'gb-sct',
                'label' => 'Scotland',
            ],

            'WAL' => [
                'flag' => 'gb-wls',
                'label' => 'Wales',
            ],

            'NIR' => [
                'flag' => 'gb-nir',
                'label' => 'Northern Ireland',
            ],

            // España
            'CAT' => [
                'flag' => 'es-ct',
                'label' => 'Catalonia',
            ],

            'BAS' => [
                'flag' => 'es-pv',
                'label' => 'Basque Country',
            ],

            'GAL' => [
                'flag' => 'es-ga',
                'label' => 'Galicia',
            ],
        ];

        if (isset($specialFlags[$code])) {
            $this->flagCode = $specialFlags[$code]['flag'];
            $this->label = $specialFlags[$code]['label'];

            return;
        }

        /*
         * Códigos ISO normales:
         *
         * ES -> es
         * US -> us
         * DE -> de
         * IM -> im
         * etc.
         */
        if (preg_match('/^[A-Z]{2}$/', $code)) {
            $this->flagCode = strtolower($code);
            $this->label = $code;

            return;
        }

        /*
         * Código desconocido.
         */
        $this->flagCode = null;
        $this->label = $code !== '' ? $code : 'Unknown';
    }

    public function render(): View
    {
        return view('components.country-flag');
    }
}
