<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(Request $request): View
    {
        $preset = (string) $request->query('preset', '');
        if ($preset !== '' && preg_match('/^[a-z0-9_]{2,64}$/', $preset)) {
            $request->session()->put('markcraft_start_preset', $preset);
        }

        return view('auth.register', [
            'startPreset' => $request->session()->get('markcraft_start_preset'),
        ]);
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        event(new Registered($user));

        Auth::login($user);

        $preset = (string) $request->input('preset', $request->session()->pull('markcraft_start_preset', ''));
        if ($preset !== '' && preg_match('/^[a-z0-9_]{2,64}$/', $preset)) {
            return redirect()->route('studio', ['preset' => $preset]);
        }

        return redirect(route('dashboard', absolute: false));
    }
}
