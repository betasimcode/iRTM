<?php

use App\Models\Workspace;
use Illuminate\Support\Facades\Auth;
use App\Services\Workspace\WorkspaceContract;


function percent($value, $decimals = 3)
{
    if (!$value) {
        return null;
    }

    return number_format($value * 100, $decimals) . '%';
}

function irClassLabel($class)
{
    return match ($class) {
        'R' => 'Rookie',
        'D' => 'Class D',
        'C' => 'Class C',
        'B' => 'Class B',
        'A' => 'Class A',
        'P' => 'Pro',
        default => null,
    };
}

if (! function_exists('themedLogo')) {

    function themedLogo($model)
    {
        $theme = Auth::user()?->theme ?? 'theme-dark';

        $logoMode = config(
            "themes.$theme.logo_mode",
            'dark'
        );

        $logo = $logoMode === 'dark'
            ? ($model->logo_dark ?? $model->logo)
            : ($model->logo_light ?? $model->logo);

        return $logo ?: 'images/placeholders/track.png';
    }
}

if (! function_exists('themedBanner')) {

    function themedBanner($model)
    {
        $theme = Auth::user()?->theme ?? 'theme-dark';

        $bannerMode = config(
            "themes.$theme.banner_mode",
            'dark'
        );

        $banner = $bannerMode === 'dark'
            ? ($model->banner_dark ?? $model->banner_dark)
            : ($model->banner_path ?? $model->banner_path);

        return $banner ?: 'images/placeholders/team.png';
    }
}

if (! function_exists('workspace')) {

    /**
     * Devuelve el Workspace activo.
     */
    function workspace(): WorkspaceContract
    {
        return app(WorkspaceContract::class);
    }
}
