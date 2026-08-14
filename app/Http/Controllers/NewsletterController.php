<?php

namespace App\Http\Controllers;

use App\Models\NewsletterSubscriber;
use App\Support\Cms;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Throwable;

class NewsletterController extends Controller
{
    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ]);

        $email = strtolower(trim($validated['email']));
        $cms = array_merge(Cms::defaults()['newsletter'] ?? [], (array) Cms::get('newsletter', []));
        $thanks = (string) ($cms['success'] ?? 'Obrigado! Seu e-mail foi cadastrado para os próximos lançamentos.');

        try {
            NewsletterSubscriber::query()->firstOrCreate(
                ['email' => $email],
                ['ip' => $request->ip()]
            );
        } catch (Throwable) {
            // Tabela ainda não migrada / DB indisponível — não quebra a home.
        }

        $to = trim((string) ($cms['to_email'] ?? config('mail.from.address', '')));
        $mailer = (string) config('mail.default');
        if ($to !== '' && $mailer !== '' && $mailer !== 'array') {
            try {
                Mail::raw(
                    "Novo lead MarkCraft: {$email}",
                    function ($message) use ($to, $email) {
                        $message->to($to)->subject('Lead newsletter MarkCraft · '.$email);
                    }
                );
            } catch (Throwable) {
                // Integração de e-mail ausente ou falhou — o visitante ainda vê sucesso.
            }
        }

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['ok' => true, 'message' => $thanks]);
        }

        return back()->with('newsletter_status', $thanks);
    }
}
