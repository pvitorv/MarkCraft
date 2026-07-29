<?php

namespace App\Http\Controllers;

use App\Models\ShortLink;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ShortLinkController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'url' => ['required', 'url', 'max:2048'],
            'utm_source' => ['nullable', 'string', 'max:120'],
            'utm_medium' => ['nullable', 'string', 'max:120'],
            'utm_campaign' => ['nullable', 'string', 'max:160'],
        ]);

        $url = $data['url'];
        $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));
        if (! in_array($scheme, ['http', 'https'], true)) {
            throw ValidationException::withMessages([
                'url' => 'Use apenas links http ou https.',
            ]);
        }

        $code = $this->uniqueCode();

        $link = ShortLink::query()->create([
            'code' => $code,
            'target_url' => $url,
            'utm_source' => $data['utm_source'] ?? null,
            'utm_medium' => $data['utm_medium'] ?? null,
            'utm_campaign' => $data['utm_campaign'] ?? null,
            'user_id' => $request->user()?->id,
        ]);

        return response()->json([
            'code' => $link->code,
            'short_url' => url('/s/'.$link->code),
            'target_url' => $link->resolvedUrl(),
        ]);
    }

    public function redirect(string $code): RedirectResponse
    {
        $link = ShortLink::query()->where('code', $code)->firstOrFail();
        $link->increment('clicks');

        return redirect()->away($link->resolvedUrl());
    }

    private function uniqueCode(): string
    {
        do {
            $code = Str::lower(Str::random(8));
        } while (ShortLink::query()->where('code', $code)->exists());

        return $code;
    }
}
