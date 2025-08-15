<?php
// Autoloader personalizado para PHPSpreadsheet
spl_autoload_register(function ($class) {
    $prefixes = [
        'PhpOffice\\PhpSpreadsheet\\' => __DIR__ . '/phpoffice/PhpSpreadsheet-5.0.0/src/PhpSpreadsheet/',
        'Psr\\SimpleCache\\' => __DIR__ . '/psr/simple-cache/src/',
        'Composer\\Pcre\\' => __DIR__ . '/composer/pcre/src/'  // NOVA LINHA
    ];
    
    foreach ($prefixes as $prefix => $base_dir) {
        if (strpos($class, $prefix) === 0) {
            $relative_class = substr($class, strlen($prefix));
            $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
            
            if (file_exists($file)) {
                require_once $file;
                return true;
            }
        }
    }
    return false;
});