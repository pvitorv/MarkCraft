<?php

namespace App\Support;

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
     * Cadastro público.
     * - web: sempre liberado
     * - desktop: liberado por padrão (testes); MARKCRAFT_ALLOW_REGISTER=false fecha
     */
    public static function allowsRegister(): bool
    {
        if (self::isWeb()) {
            return true;
        }

        $forced = config('markcraft.shell.allow_register');
        if ($forced === false || $forced === 'false' || $forced === '0' || $forced === 0) {
            return false;
        }
        if ($forced === true || $forced === 'true' || $forced === '1' || $forced === 1) {
            return true;
        }

        // null / vazio no desktop = aberto (fase de testes com pessoas reais)
        return true;
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
