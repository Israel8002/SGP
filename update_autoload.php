<?php
/**
 * Script para actualizar todos los archivos PHP para usar el autoloader
 */

$files = [
    'dashboard.php',
    'users.php', 
    'profile.php',
    'shifts.php',
    'reports.php',
    'authorizations.php',
    'logout.php',
    'register.php'
];

foreach ($files as $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        
        // Reemplazar require_once 'classes/Auth.php' con autoload
        $content = preg_replace(
            "/require_once\s+['\"]classes\/Auth\.php['\"];/",
            "require_once 'autoload.php';\n\nuse App\Auth;",
            $content
        );
        
        // Reemplazar require_once 'classes/Security.php' con autoload
        $content = preg_replace(
            "/require_once\s+['\"]classes\/Security\.php['\"];/",
            "require_once 'autoload.php';\n\nuse App\Security;",
            $content
        );
        
        // Si no tiene autoload pero tiene require_once de classes, agregarlo
        if (strpos($content, "require_once 'autoload.php'") === false && 
            (strpos($content, "require_once 'classes/") !== false || strpos($content, 'require_once "classes/') !== false)) {
            
            // Agregar autoload después de la primera línea de comentario
            $content = preg_replace(
                "/(<\?php\s*\/\*\*[^*]*\*\/\s*)/",
                "$1\nrequire_once 'autoload.php';\n",
                $content
            );
        }
        
        file_put_contents($file, $content);
        echo "Actualizado: $file\n";
    }
}

echo "Actualización completada.\n";
?>
