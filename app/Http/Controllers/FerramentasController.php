<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class FerramentasController extends Controller
{
    /** @var array<string, array{name: string, blurb: string, status: string}> */
    private array $tools = [
        'encurtador' => [
            'name' => 'Encurtador de URL',
            'blurb' => 'Encurte links de campanha com UTM opcional.',
            'status' => 'em breve',
        ],
        'conversor-imagens' => [
            'name' => 'Conversor de imagens',
            'blurb' => 'PNG ↔ JPG ↔ WebP em lote simples.',
            'status' => 'em breve',
        ],
        'conversor-pdf' => [
            'name' => 'Conversor de PDF',
            'blurb' => 'PDF ↔ imagens para posts e propostas.',
            'status' => 'em breve',
        ],
        'compressor-pdf' => [
            'name' => 'Compressor de PDF',
            'blurb' => 'Reduza tamanho mantendo legibilidade.',
            'status' => 'em breve',
        ],
    ];

    public function index(): View
    {
        return view('ferramentas.index', ['tools' => $this->tools]);
    }

    public function show(string $slug): View|RedirectResponse
    {
        if (! isset($this->tools[$slug])) {
            return redirect()->route('ferramentas.index');
        }

        return view('ferramentas.show', [
            'tool' => $this->tools[$slug],
            'slug' => $slug,
        ]);
    }
}
