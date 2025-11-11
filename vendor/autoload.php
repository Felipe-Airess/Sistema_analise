<?php
/**
 * Minimal autoloader for the bundled dompdf library when Composer isn't available.
 *
 * This file maps the Dompdf namespace to the `vendor/src/` and `vendor/lib/`
 * directories and requires any top-level PHP files in `vendor/lib/` (e.g. Cpdf.php).
 * It's a pragmatic fallback so scripts that do `require 'vendor/autoload.php'`
 * (like your `exportar_pdf.php`) work without running Composer.
 */

spl_autoload_register(function ($class) {
    // PSR-4 like mapping for the Dompdf namespace
    $prefix = 'Dompdf\\';
    if (strpos($class, $prefix) === 0) {
        $relative = substr($class, strlen($prefix));
        $relative_path = str_replace('\\', '/', $relative) . '.php';
        $baseDirs = [__DIR__ . '/src/', __DIR__ . '/lib/'];
        foreach ($baseDirs as $base) {
            $file = $base . $relative_path;
            if (file_exists($file)) {
                require $file;
                return true;
            }
        }
    }

    // Try a FontLib mapping (some dompdf builds reference FontLib) -> vendor/lib
    $prefix2 = 'FontLib\\';
    if (strpos($class, $prefix2) === 0) {
        $relative = substr($class, strlen($prefix2));
        $file = __DIR__ . '/lib/' . str_replace('\\', '/', $relative) . '.php';
        if (file_exists($file)) {
            require $file;
            return true;
        }
    }

    return false;
});

// Require any top-level PHP files in vendor/lib (useful for legacy files like Cpdf.php)
foreach (glob(__DIR__ . '/lib/*.php') as $file) {
    require_once $file;
}

// Also require all PHP files under vendor/src recursively. Many dompdf classes live
// under vendor/src and, since Composer is not available, including them ensures
// the classes are defined.
$rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(__DIR__ . '/src'));
foreach ($rii as $file) {
    if ($file->isDir()) continue;
    if (pathinfo($file->getPathname(), PATHINFO_EXTENSION) === 'php') {
        require_once $file->getPathname();
    }
}

// If other third-party namespaces are required by dompdf and missing, they will
// trigger errors. For full compatibility it's recommended to install Composer
// and use the official autoloader. This file exists as a pragmatic fallback.

return true;
