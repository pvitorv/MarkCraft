<?php

namespace App\Support;

use App\Models\User;

/**
 * Modo web (público) vs desktop (cópia local: login → Studio).
 */
class MarkCraftShell
{
    public static function mode(): string
    {
        $mode = strtolower((string) config('markcraft.shell.mode', 'web'));

        return in_array($mode, ['web', 'desktop'], true) ? $mode : 'web';
    }

    public static function isDesktop(): bool
    {
        return self::mode() === 'desktop';
    }

    public static function isWeb(): bool
    {
        return ! self::isDesktop();
    }

    /** Rota nomeada pós-login / “home” efetiva. */
    public static function homeRouteName(): string
    {
        return self::isDesktop() ? 'studio' : 'home';
    }

    public static function homeUrl(): string
    {
        return route(self::homeRouteName(), absolute: false);
    }

    /**
     * No desktop: register só se nenhum usuário existir (bootstrap),
     * a menos que MARKCRAFT_ALLOW_REGISTER force true/false.
     */
    public static function allowsRegister(): bool
    {
        if (self::isWeb()) {
            return true;
        }

        $forced = config('markcraft.shell.allow_register');
        if ($forced === true || $forced === 'true' || $forced === '1' || $forced === 1) {
            return true;
        }
        if ($forced === false || $forced === 'false' || $forced === '0' || $forced === 0) {
            return false;
        }

        try {
            return User::query()->count() === 0;
        } catch (\Throwable) {
            return true;
        }
    }

    /** Categoria de pasta pai para exports no Electron. */
    public static function exportCategoryForDeckKind(?string $kind): string
    {
        return match ($kind) {
            'social' => 'Instagram',
            'web' => 'Blog',
            'presentation' => 'Apresentacao',
            default => 'Outros',
        };
    }
}
