<?php

/**
 * Compacta o conteúdo de public/ → public/markcraft-public-deploy.zip
 * Uso: php scripts/make-public-deploy-zip.php
 */

declare(strict_types=1);

$root = dirname(__DIR__);
$public = $root.'/public';
$zipPath = $public.'/markcraft-public-deploy.zip';
$zipName = 'markcraft-public-deploy.zip';
$tempZip = $root.'/storage/app/markcraft-public-deploy.zip.tmp';

if (! is_dir(dirname($tempZip))) {
    mkdir(dirname($tempZip), 0755, true);
}

foreach ([$zipPath, $tempZip] as $old) {
    if (is_file($old)) {
        unlink($old);
    }
}

$zip = new ZipArchive();
if ($zip->open($tempZip, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
    fwrite(STDERR, "ERRO: não foi possível criar {$tempZip}\n");
    exit(1);
}

$base = realpath($public);
if ($base === false) {
    fwrite(STDERR, "ERRO: pasta public/ não encontrada\n");
    exit(1);
}

$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($base, FilesystemIterator::SKIP_DOTS),
    RecursiveIteratorIterator::SELF_FIRST
);

$fileCount = 0;
foreach ($iterator as $file) {
    /** @var SplFileInfo $file */
    $path = $file->getRealPath();
    if ($path === false) {
        continue;
    }
    $basePrefix = str_replace('\\', '/', $base.'/');
    $normalizedPath = str_replace('\\', '/', $path);
    if (! str_starts_with($normalizedPath, $basePrefix)) {
        continue;
    }
    if (basename($path) === $zipName) {
        continue;
    }
    if ($file->isLink()) {
        continue;
    }
    $relative = substr($path, strlen($base) + 1);
    $relative = str_replace('\\', '/', $relative);
    if ($relative === '' || str_starts_with($relative, '../') || str_contains($relative, '/../')) {
        continue;
    }
    if ($relative === 'storage' || str_starts_with($relative, 'storage/')) {
        continue;
    }
    if ($file->isDir()) {
        $zip->addEmptyDir($relative);
        continue;
    }
    $contents = file_get_contents($path);
    if ($contents === false) {
        fwrite(STDERR, "AVISO: não foi possível ler {$relative}\n");
        continue;
    }
    $zip->addFromString($relative, $contents);
    $fileCount++;
}

if (! $zip->close()) {
    fwrite(STDERR, "ERRO: falha ao finalizar zip ({$fileCount} arquivos)\n");
    exit(1);
}

if (! rename($tempZip, $zipPath)) {
    fwrite(STDERR, "ERRO: não foi possível mover zip para {$zipPath}\n");
    exit(1);
}

$sizeMb = round(filesize($zipPath) / 1024 / 1024, 2);
echo "OK: {$zipPath} ({$sizeMb} MB, {$fileCount} arquivos)\n";
