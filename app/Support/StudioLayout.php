<?php

namespace App\Support;

use Illuminate\Http\Request;

/**
 * Escolhe shell desktop vs mobile do Image Studio.
 * pointer:coarse não existe no servidor — UA + Sec-CH-UA-Mobile; override ?layout=mobile|desktop.
 */
class StudioLayout
{
    public static function usesMobileStudio(Request $request): bool
    {
        if (MarkCraftShell::isDesktop()) {
            return false;
        }

        $layout = strtolower((string) $request->query('layout', ''));
        if ($layout === 'desktop') {
            return false;
        }
        if ($layout === 'mobile') {
            return true;
        }

        if ($request->header('Sec-CH-UA-Mobile') === '?1') {
            return true;
        }

        return self::userAgentLooksMobile((string) $request->userAgent());
    }

    public static function layoutName(Request $request): string
    {
        return self::usesMobileStudio($request) ? 'mobile' : 'desktop';
    }

    private static function userAgentLooksMobile(string $ua): bool
    {
        if ($ua === '') {
            return false;
        }

        return (bool) preg_match(
            '/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini|Mobile/i',
            $ua
        );
    }
}
