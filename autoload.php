<?php
/**
 * Autoloader para SGE v2
 * Carga automáticamente las clases del sistema
 */

spl_autoload_register(function ($class) {
    // Convertir namespace a ruta de archivo
    $class = str_replace('App\\', '', $class);
    $file = __DIR__ . '/classes/' . $class . '.php';
    
    if (file_exists($file)) {
        require_once $file;
    }
});

// Cargar configuración de base de datos
require_once __DIR__ . '/config/database.php';
?>
