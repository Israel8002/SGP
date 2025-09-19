<?php
// Redirigir todas las rutas a los archivos PHP correspondientes
$request_uri = $_SERVER['REQUEST_URI'];
$path = parse_url($request_uri, PHP_URL_PATH);

// Remover la barra inicial
$path = ltrim($path, '/');

// Si no hay path o es la raíz, mostrar el index principal
if (empty($path) || $path === 'api') {
    include_once '../index.php';
    return;
}

// Mapear rutas a archivos PHP
$routes = [
    'login' => '../login.php',
    'dashboard' => '../dashboard.php',
    'users' => '../users.php',
    'profile' => '../profile.php',
    'shifts' => '../shifts.php',
    'reports' => '../reports.php',
    'authorizations' => '../authorizations.php',
    'logout' => '../logout.php',
    'register' => '../register.php'
];

// Si la ruta existe en el mapeo, incluir el archivo
if (isset($routes[$path])) {
    include_once $routes[$path];
} else {
    // Si no existe, intentar incluir el archivo directamente
    $file_path = '../' . $path . '.php';
    if (file_exists($file_path)) {
        include_once $file_path;
    } else {
        // Mostrar 404 o redirigir al index
        include_once '../index.php';
    }
}
?>
