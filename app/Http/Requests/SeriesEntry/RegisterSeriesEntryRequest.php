<?php

namespace App\Http\Requests\SeriesEntry;

use Illuminate\Foundation\Http\FormRequest;

class RegisterSeriesEntryRequest extends FormRequest
{
    /**
     * Determina si el usuario puede realizar
     * esta petición.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Reglas de validación.
     */
    public function rules(): array
    {
        return [

            'workspace_id' => [
                'required',
                'integer',
                'exists:workspaces,id'
            ],

            'series_id' => [
                'required',
                'integer',
                'exists:iracing_series,id'
            ],

            'competition_car_id' => [
                'required',
                'integer',
                'exists:cars,id'
            ],

            'members' => [
                'required',
                'array',
                'min:1'
            ],

            'members.*' => [
                'integer',
                'exists:users,id'
            ]

        ];
    }
}
