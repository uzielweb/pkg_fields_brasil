<?php

/**
 * Automated Build & Packaging Script for pkg_fields_brasil
 *
 * Packages all 6 plugins into individual distribution zips, and packages
 * them into the final installable Joomla 6 master package zip.
 *
 * @copyright (C) 2026 Uziel Almeida Oliveira <https://github.com/uzielweb>
 * @license   GNU General Public License version 2 or later; see LICENSE
 */

$version = '1.0.0';
$rootDir = __DIR__;
$packagesDir = $rootDir . '/packages';

if (!is_dir($packagesDir)) {
    mkdir($packagesDir, 0755, true);
}

$plugins = [
    'cpf',
    'cnpj',
    'cep',
    'telefone',
    'cpfcnpj',
    'pix'
];

/**
 * Helper function to recursively zip a folder.
 *
 * @param   string     $sourceDir
 * @param   string     $outZipPath
 * @param   string     $subPathInZip
 * @return  bool
 */
function createZipArchive(string $sourceDir, string $outZipPath, string $subPathInZip = ''): bool
{
    if (file_exists($outZipPath)) {
        unlink($outZipPath);
    }

    $zip = new ZipArchive();
    if ($zip->open($outZipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
        return false;
    }

    $sourceDir = rtrim($sourceDir, '/\\');
    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($sourceDir, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::LEAVES_ONLY
    );

    foreach ($files as $file) {
        if (!$file->isFile()) {
            continue;
        }

        $filePath = $file->getRealPath();
        $relativePath = substr($filePath, strlen($sourceDir) + 1);
        $relativePath = str_replace('\\', '/', $relativePath);

        $localName = $subPathInZip ? ($subPathInZip . '/' . $relativePath) : $relativePath;
        $zip->addFile($filePath, $localName);
    }

    return $zip->close();
}

echo "=============================================\n";
echo "Building pkg_fields_brasil v$version\n";
echo "=============================================\n\n";

// 1. Build each individual plugin zip
foreach ($plugins as $plugin) {
    $pluginDir = $rootDir . '/plugins/fields/' . $plugin;
    $zipFile = $packagesDir . '/plg_fields_' . $plugin . '.zip';

    echo "Compressing plugin [$plugin] -> " . basename($zipFile) . " ... ";
    if (createZipArchive($pluginDir, $zipFile)) {
        echo "OK (" . round(filesize($zipFile) / 1024, 2) . " KB)\n";
    } else {
        echo "FAILED!\n";
        exit(1);
    }
}

// 2. Build the master Joomla package zip
$packageZipPath = $rootDir . '/pkg_fields_brasil_v' . $version . '.zip';
if (file_exists($packageZipPath)) {
    unlink($packageZipPath);
}

echo "\nAssembling master package zip -> " . basename($packageZipPath) . " ... ";

$pkgZip = new ZipArchive();
if ($pkgZip->open($packageZipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
    echo "FAILED to create master zip!\n";
    exit(1);
}

// Add package manifest
$pkgZip->addFile($rootDir . '/pkg_fields_brasil.xml', 'pkg_fields_brasil.xml');

// Add package language files
$langFiles = [
    'language/pt-BR/pkg_fields_brasil.sys.ini',
    'language/en-GB/pkg_fields_brasil.sys.ini'
];
foreach ($langFiles as $lf) {
    $fullPath = $rootDir . '/' . $lf;
    if (file_exists($fullPath)) {
        $pkgZip->addFile($fullPath, $lf);
    }
}

// Add each plugin zip inside packages/
foreach ($plugins as $plugin) {
    $subZip = $packagesDir . '/plg_fields_' . $plugin . '.zip';
    $pkgZip->addFile($subZip, 'packages/plg_fields_' . $plugin . '.zip');
}

$pkgZip->close();

echo "OK!\n";
echo "Package successfully generated at:\n  $packageZipPath (" . round(filesize($packageZipPath) / 1024, 2) . " KB)\n\n";
echo "Build completed with 100% success!\n";
