<?php

namespace App\Services\ImageStudio;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;
use Illuminate\Process\ProcessResult;

/**
 * Remoção de fundo via rembg (Python) — alternativa open-source à @imgly (AGPL).
 * Pacote npm da IMG.LY permanece instalado, mas só é usado se o driver for "imgly".
 */
class BackgroundRemovalService
{
    public function driver(): string
    {
        $driver = strtolower((string) config('image_studio.background_removal.driver', 'rembg'));

        return in_array($driver, ['rembg', 'imgly', 'off'], true) ? $driver : 'rembg';
    }

    public function usesClientLibrary(): bool
    {
        return $this->driver() === 'imgly';
    }

    public function isAvailable(): bool
    {
        return match ($this->driver()) {
            'imgly' => true,
            // Python encontrado = disponível; o import rembg é validado no remove()
            // (Cache::remember com false envenenava o gate por 30 min).
            'rembg' => $this->pythonBinary() !== null,
            default => false,
        };
    }

    /**
     * @return array{driver: string, available: bool, client: bool, label: string}
     */
    public function status(): array
    {
        $driver = $this->driver();
        $available = $this->isAvailable();
        $rembgReady = $driver === 'rembg' && $available && $this->rembgImportOk();

        return [
            'driver' => $driver,
            'available' => $available,
            'client' => $driver === 'imgly',
            'label' => match ($driver) {
                'imgly' => 'Browser (IMG.LY — isolada; ative só se tiver licença)',
                'rembg' => $rembgReady
                    ? 'Servidor (rembg / open source)'
                    : ($available
                        ? 'Servidor (rembg) — Python ok; 1ª remoção pode demorar'
                        : 'Servidor (rembg) — instale: pip install rembg pillow'),
                default => 'Desligada',
            },
        ];
    }

    public function remove(string $inputPath, string $outputPath): void
    {
        if ($this->driver() !== 'rembg') {
            throw new \RuntimeException('Driver rembg não está ativo.');
        }

        if (! file_exists($inputPath)) {
            throw new \InvalidArgumentException('Arquivo de entrada não encontrado.');
        }

        File::ensureDirectoryExists(dirname($outputPath));

        $python = $this->pythonBinary();
        if (! $python) {
            throw new \RuntimeException(
                'Python não encontrado. Defina REMBG_PYTHON no .env (ex.: C:/laragon/bin/python/python-3.10/python.exe)'
            );
        }

        if (! $this->rembgImportOk()) {
            throw new \RuntimeException(
                'Pacote rembg não encontrado nesse Python. Rode: "'.$python.'" -m pip install rembg pillow onnxruntime'
            );
        }

        $script = base_path('scripts/remove-background.py');
        if (! file_exists($script)) {
            throw new \RuntimeException('Script scripts/remove-background.py não encontrado.');
        }

        $result = $this->runPython(180, [$python, $script, $inputPath, $outputPath]);

        if (! $result->successful() || ! file_exists($outputPath)) {
            $msg = trim($result->errorOutput() ?: $result->output()) ?: 'Falha ao remover fundo com rembg.';
            Log::warning('Image Studio rembg falhou', [
                'python' => $python,
                'exit' => $result->exitCode(),
                'error' => $msg,
            ]);
            throw new \RuntimeException($msg);
        }

        Cache::put($this->availabilityCacheKey(), true, now()->addMinutes(60));
    }

    protected function rembgImportOk(): bool
    {
        $python = $this->pythonBinary();
        if (! $python) {
            return false;
        }

        // Só confiar em cache positivo — false não pode ficar “preso”.
        if (Cache::get($this->availabilityCacheKey()) === true) {
            return true;
        }

        $check = $this->runPython(45, [$python, '-c', 'import rembg']);
        if ($check->successful()) {
            Cache::put($this->availabilityCacheKey(), true, now()->addMinutes(60));

            return true;
        }

        Log::info('Image Studio rembg import falhou', [
            'python' => $python,
            'error' => trim($check->errorOutput() ?: $check->output()),
        ]);

        return false;
    }

    protected function availabilityCacheKey(): string
    {
        $python = (string) (config('image_studio.background_removal.python') ?: env('REMBG_PYTHON') ?: 'default');

        return 'image_studio.rembg_ok.'.md5($python);
    }

    /**
     * @param  list<string>  $command
     */
    protected function runPython(int $timeout, array $command): ProcessResult
    {
        return Process::timeout($timeout)
            ->env($this->pythonEnvironment())
            ->run($command);
    }

    /**
     * Ambiente mínimo no Windows para o Python iniciar sob Apache/Laragon.
     * Sem SystemRoot: "_Py_HashRandomization_Init: failed to get random numbers".
     *
     * @return array<string, string>
     */
    protected function pythonEnvironment(): array
    {
        $inherited = [];
        foreach ([$_ENV, $_SERVER] as $bag) {
            foreach ($bag as $key => $value) {
                if (is_string($key) && is_string($value) && $key !== '' && ! str_starts_with($key, 'HTTP_')) {
                    $inherited[$key] = $value;
                }
            }
        }

        $systemRoot = $inherited['SystemRoot']
            ?? $inherited['SYSTEMROOT']
            ?? (getenv('SystemRoot') ?: (getenv('SYSTEMROOT') ?: 'C:\\Windows'));
        $path = $inherited['PATH'] ?? (getenv('PATH') ?: ($systemRoot.'\\System32'));
        $temp = $inherited['TEMP'] ?? ($inherited['TMP'] ?? (getenv('TEMP') ?: (getenv('TMP') ?: sys_get_temp_dir())));

        $env = array_merge($inherited, [
            'SystemRoot' => $systemRoot,
            'SYSTEMROOT' => $systemRoot,
            'WINDIR' => $inherited['WINDIR'] ?? (getenv('WINDIR') ?: $systemRoot),
            'PATH' => $path,
            'TEMP' => $temp,
            'TMP' => $temp,
            'PYTHONIOENCODING' => 'utf-8',
            // Evita depender de CSPRNG do SO na inicialização do hash (Apache costuma falhar).
            'PYTHONHASHSEED' => '0',
            'PYTHONUTF8' => '1',
            'PYTHONUNBUFFERED' => '1',
        ]);

        // Home do modelo u2net (~/.u2net) — se Apache não tiver USERPROFILE, usa storage.
        if (empty($env['USERPROFILE'])) {
            $home = storage_path('app/tmp/python-home');
            File::ensureDirectoryExists($home);
            $env['USERPROFILE'] = $home;
            $env['HOME'] = $home;
        }

        return $env;
    }

    protected function pythonBinary(): ?string
    {
        foreach ([
            config('image_studio.background_removal.python'),
            env('REMBG_PYTHON'),
            // Preferir o Python do Laragon no Windows (evita stub da Windows Store).
            'C:\\laragon\\bin\\python\\python-3.10\\python.exe',
            'C:/laragon/bin/python/python-3.10/python.exe',
            'python3',
            'python',
            'py',
        ] as $candidate) {
            if (! is_string($candidate) || $candidate === '') {
                continue;
            }
            // Windows: `py -3` precisa de args separados — usamos só binários simples.
            if (str_contains($candidate, ' ')) {
                continue;
            }

            $normalized = $this->normalizePythonPath($candidate);
            if (! in_array(strtolower($normalized), ['python', 'python3', 'py'], true)
                && ! is_file($normalized)) {
                continue;
            }
            $probe = $this->runPython(8, [$normalized, '--version']);
            if ($probe->successful()) {
                return $normalized;
            }
        }

        return null;
    }

    protected function normalizePythonPath(string $path): string
    {
        if (in_array(strtolower($path), ['python', 'python3', 'py'], true)) {
            return $path;
        }

        // Windows aceita /, mas Process fica mais estável com \.
        return str_replace('/', DIRECTORY_SEPARATOR, $path);
    }
}
