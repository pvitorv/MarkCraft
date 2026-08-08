<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Support\MarkCraftShell;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(Request $request): View
    {
        $this->captureStartPreset($request);

        return view('auth.login', [
            'startPreset' => $request->session()->get('markcraft_start_preset'),
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $preset = (string) $request->session()->pull('markcraft_start_preset', '');
        if ($preset === '' || ! preg_match('/^[a-z0-9_]{2,64}$/', $preset)) {
            $intended = (string) $request->session()->get('url.intended', '');
            if ($intended !== '' && preg_match('/[?&]preset=([a-z0-9_]{2,64})/i', $intended, $m)) {
                $preset = $m[1];
                // Evita intended genérico sem query depois do redirect explícito
                $request->session()->forget('url.intended');
            } else {
                $preset = '';
            }
        }

        if ($preset !== '' && preg_match('/^[a-z0-9_]{2,64}$/', $preset)) {
            return redirect()->route('studio', ['preset' => $preset]);
        }

        return redirect()->intended(MarkCraftShell::homeUrl());
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect(MarkCraftShell::isDesktop() ? route('login') : '/');
    }

    /** Preserva preset da query ou da URL intended (atalhos da home). */
    private function captureStartPreset(Request $request): void
    {
        $preset = (string) $request->query('preset', '');
        if ($preset === '' || ! preg_match('/^[a-z0-9_]{2,64}$/', $preset)) {
            $intended = (string) $request->session()->get('url.intended', '');
            if ($intended !== '' && preg_match('/[?&]preset=([a-z0-9_]{2,64})/i', $intended, $m)) {
                $preset = $m[1];
            }
        }
        if ($preset !== '' && preg_match('/^[a-z0-9_]{2,64}$/', $preset)) {
            $request->session()->put('markcraft_start_preset', $preset);
        }
    }
}
