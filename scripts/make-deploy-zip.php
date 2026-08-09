<?php

/**
 * Compacta deploy-staging/markcraft → markcraft-deploy-YYYYMMDD-hostoo.zip
 * Uso: php scripts/make-deploy-zip.php
 */

declare(strict_types=1);

$root = dirname(__DIR__);
$staging = $root.'/deploy-staging/markcraft';

if (! is_dir($staging) || ! is_file($staging.'/artisan')) {
    fwrite(STDERR, "ERRO: deploy-staging/markcraft ausente. Rode scripts/build-deploy-zip.sh primeiro (até antes do zip).\n");
    exit(1);
}

$zipName = 'markcraft-deploy-'.date('Ymd').'-hostoo.zip';
$zipPath = $root.'/'.$zipName;

if (is_file($zipPath)) {
    unlink($zipPath);
}

$zip = new ZipArchive();
if ($zip->open($zipPath, ZipArchive::CREATE) !== true) {
    fwrite(STDERR, "ERRO: não foi possível criar {$zipPath}\n");
    exit(1);
}

$base = realpath($staging);
$prefix = 'markcraft';
$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($base, FilesystemIterator::SKIP_DOTS),
    RecursiveIteratorIterator::SELF_FIRST
);

foreach ($iterator as $file) {
    /** @var SplFileInfo $file */
    $path = $file->getRealPath();
    if ($path === false) {
        continue;
    }
    $relative = substr($path, strlen($base) + 1);
    $relative = str_replace('\\', '/', $relative);
    $entry = $prefix.'/'.$relative;

    if ($file->isDir()) {
        $zip->addEmptyDir(rtrim($entry, '/'));
    } else {
        $zip->addFile($path, $entry);
    }
}

$zip->close();

$sizeMb = round(filesize($zipPath) / 1024 / 1024, 2);
echo "OK: {$zipPath} ({$sizeMb} MB)\n";
